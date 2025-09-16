<?php
/**
 * Claude API Client
 *
 * @package EightyFourEM\LocalPages\Api
 */

namespace EightyFourEM\LocalPages\Api;

use EightyFourEM\LocalPages\Contracts\ApiClientInterface;

/**
 * Client for interacting with Claude API
 */
class ClaudeApiClient implements ApiClientInterface {
    /**
     * API endpoint
     */
    private const API_ENDPOINT = 'https://api.anthropic.com/v1/messages';

    /**
     * API version
     */
    private const API_VERSION = '2023-06-01';

    /**
     * Model to use
     */
    private const MODEL = 'claude-sonnet-4-20250514';

    /**
     * Max tokens for response
     */
    private const MAX_TOKENS = 4000;

    /**
     * Request timeout in seconds
     */
    private const TIMEOUT = 600; // 10 minutes

    /**
     * Maximum retry attempts
     */
    private const MAX_RETRIES = 5;

    /**
     * Initial retry delay in seconds
     */
    private const INITIAL_RETRY_DELAY = 1;

    /**
     * Maximum retry delay in seconds
     */
    private const MAX_RETRY_DELAY = 60;

    /**
     * API key manager
     *
     * @var ApiKeyManager
     */
    private ApiKeyManager $keyManager;

    /**
     * Constructor
     *
     * @param  ApiKeyManager  $keyManager  API key manager
     */
    public function __construct( ApiKeyManager $keyManager ) {
        $this->keyManager = $keyManager;
    }

    /**
     * Send a request to Claude API
     *
     * @param  string  $prompt  The prompt to send
     *
     * @return string|false Response content or false on failure
     */
    public function sendRequest( string $prompt ): string|false {
        if ( ! $this->isConfigured() ) {
            $this->logError( 'API client is not properly configured' );
            return false;
        }

        $api_key = $this->keyManager->getKey();
        if ( false === $api_key ) {
            $this->logError( 'Failed to retrieve API key' );
            return false;
        }

        $body = [
            'model'      => self::MODEL,
            'max_tokens' => self::MAX_TOKENS,
            'messages'   => [
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
        ];

        $args = [
            'method'  => 'POST',
            'timeout' => self::TIMEOUT,
            'headers' => [
                'Content-Type'      => 'application/json',
                'x-api-key'         => $api_key,
                'anthropic-version' => self::API_VERSION,
            ],
            'body'    => wp_json_encode( $body ),
        ];

        // Retry logic with exponential backoff
        $attempt = 0;
        $retry_delay = self::INITIAL_RETRY_DELAY;
        $last_error = '';

        while ( $attempt < self::MAX_RETRIES ) {
            $attempt++;

            if ( $attempt > 1 ) {
                $this->logInfo( "Retrying API request (attempt {$attempt}/" . self::MAX_RETRIES . ") after {$retry_delay} seconds delay" );
                sleep( $retry_delay );
            }

            $response = wp_remote_post( self::API_ENDPOINT, $args );

            // Handle WordPress errors (network issues, timeouts, etc.)
            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                $last_error = "Network error: {$error_message}";

                // Check if error is retryable
                if ( $this->isRetryableError( $error_message ) ) {
                    $this->logWarning( "Retryable error on attempt {$attempt}: {$error_message}" );
                    $retry_delay = min( $retry_delay * 2, self::MAX_RETRY_DELAY );
                    continue;
                }

                // Non-retryable error
                $this->logError( "Non-retryable error: {$error_message}" );
                return false;
            }

            $response_code = wp_remote_retrieve_response_code( $response );
            $response_body = wp_remote_retrieve_body( $response );

            // Handle successful response
            if ( 200 === $response_code ) {
                $data = json_decode( $response_body, true );

                if ( ! isset( $data['content'][0]['text'] ) ) {
                    $this->logError( 'Unexpected API response format: ' . wp_json_encode( $data ) );
                    return false;
                }

                if ( $attempt > 1 ) {
                    $this->logInfo( "API request succeeded on attempt {$attempt}" );
                }

                return $data['content'][0]['text'];
            }

            // Handle HTTP errors
            $last_error = "HTTP {$response_code}: {$response_body}";

            // Check if HTTP status code is retryable
            if ( $this->isRetryableHttpStatus( $response_code ) ) {
                $this->logWarning( "Retryable HTTP error on attempt {$attempt}: {$last_error}" );
                $retry_delay = min( $retry_delay * 2, self::MAX_RETRY_DELAY );
                continue;
            }

            // Handle rate limiting (429) with Retry-After header
            if ( 429 === $response_code ) {
                $headers = wp_remote_retrieve_headers( $response );
                $retry_after = isset( $headers['retry-after'] ) ? (int) $headers['retry-after'] : $retry_delay * 2;
                $retry_delay = min( $retry_after, self::MAX_RETRY_DELAY );
                $this->logWarning( "Rate limited on attempt {$attempt}. Retry after {$retry_delay} seconds" );
                continue;
            }

            // Non-retryable HTTP error
            $this->logError( "Non-retryable HTTP error: {$last_error}" );
            $this->logApiErrorDetails( $response_code, $response_body );
            return false;
        }

        // All retries exhausted
        $this->logError( "API request failed after " . self::MAX_RETRIES . " attempts. Last error: {$last_error}" );
        return false;
    }

    /**
     * Check if the API client is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool {
        return $this->keyManager->hasKey();
    }

    /**
     * Validate API credentials
     *
     * @return bool
     */
    public function validateCredentials(): bool {
        if ( ! $this->isConfigured() ) {
            return false;
        }

        // Send a simple test request
        $response = $this->sendRequest( 'Reply with just the word "OK" if you receive this message.' );

        return false !== $response && str_contains( strtoupper( $response ), 'OK' );
    }

    /**
     * Check if an error is retryable
     *
     * @param  string  $error_message  Error message from WP_Error
     *
     * @return bool
     */
    private function isRetryableError( string $error_message ): bool {
        $retryable_patterns = [
            'timeout',
            'timed out',
            'connection reset',
            'connection refused',
            'could not resolve host',
            'name or service not known',
            'temporary failure',
            'network is unreachable',
        ];

        $error_lower = strtolower( $error_message );
        foreach ( $retryable_patterns as $pattern ) {
            if ( str_contains( $error_lower, $pattern ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an HTTP status code is retryable
     *
     * @param  int  $status_code  HTTP status code
     *
     * @return bool
     */
    private function isRetryableHttpStatus( int $status_code ): bool {
        // Retry on server errors and rate limiting
        $retryable_codes = [
            429, // Too Many Requests
            500, // Internal Server Error
            502, // Bad Gateway
            503, // Service Unavailable
            504, // Gateway Timeout
            507, // Insufficient Storage
            509, // Bandwidth Limit Exceeded
            529, // Overloaded
        ];

        return in_array( $status_code, $retryable_codes, true );
    }

    /**
     * Log API error details
     *
     * @param  int     $status_code    HTTP status code
     * @param  string  $response_body  Response body
     */
    private function logApiErrorDetails( int $status_code, string $response_body ): void {
        // Try to parse error details from response
        $data = json_decode( $response_body, true );

        if ( isset( $data['error'] ) ) {
            $error = $data['error'];

            if ( is_array( $error ) ) {
                $error_type = $error['type'] ?? 'unknown';
                $error_message = $error['message'] ?? 'No error message';
                $this->logError( "API Error Type: {$error_type}, Message: {$error_message}" );
            } else {
                $this->logError( "API Error: {$error}" );
            }
        }

        // Log specific guidance based on status code
        switch ( $status_code ) {
            case 400:
                $this->logError( 'Bad Request: Check the request format and parameters' );
                break;
            case 401:
                $this->logError( 'Unauthorized: Check your API key' );
                break;
            case 403:
                $this->logError( 'Forbidden: API key may lack necessary permissions' );
                break;
            case 404:
                $this->logError( 'Not Found: Check the API endpoint URL' );
                break;
            case 413:
                $this->logError( 'Payload Too Large: Request body exceeds size limit' );
                break;
            case 422:
                $this->logError( 'Unprocessable Entity: Request validation failed' );
                break;
        }
    }

    /**
     * Log an error message
     *
     * @param  string  $message  Error message
     */
    private function logError( string $message ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[84EM Local Pages] Claude API Error: ' . $message );
        }

        if ( defined( 'WP_CLI' ) && WP_CLI && ! $this->isTestContext() ) {
            \WP_CLI::warning( $message );
        }
    }

    /**
     * Log a warning message
     *
     * @param  string  $message  Warning message
     */
    private function logWarning( string $message ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[84EM Local Pages] Claude API Warning: ' . $message );
        }

        if ( defined( 'WP_CLI' ) && WP_CLI && ! $this->isTestContext() ) {
            \WP_CLI::debug( $message );
        }
    }

    /**
     * Log an info message
     *
     * @param  string  $message  Info message
     */
    private function logInfo( string $message ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( '[84EM Local Pages] Claude API Info: ' . $message );
        }

        if ( defined( 'WP_CLI' ) && WP_CLI && ! $this->isTestContext() ) {
            \WP_CLI::debug( $message );
        }
    }

    /**
     * Check if we're in a test context
     *
     * @return bool
     */
    private function isTestContext(): bool {
        // Check if we're running tests
        if ( defined( 'EIGHTYFOUREM_TESTING' ) && EIGHTYFOUREM_TESTING ) {
            return true;
        }

        // Check if the current WP-CLI command is a test command
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            $args = $_SERVER['argv'] ?? [];
            foreach ( $args as $arg ) {
                if ( strpos( $arg, '--test' ) !== false ) {
                    return true;
                }
            }
        }

        return false;
    }

}
