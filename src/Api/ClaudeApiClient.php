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
    private const TIMEOUT = ( 10 * MINUTE_IN_SECONDS );

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

        $response = wp_remote_post( self::API_ENDPOINT, $args );

        if ( is_wp_error( $response ) ) {
            $this->logError( 'API request failed: ' . $response->get_error_message() );
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        if ( 200 !== $response_code ) {
            $this->logError( "API returned status code {$response_code}: {$response_body}" );
            return false;
        }

        $data = json_decode( $response_body, true );

        if ( ! isset( $data['content'][0]['text'] ) ) {
            $this->logError( 'Unexpected API response format' );
            return false;
        }

        return $data['content'][0]['text'];
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
     * Log an error message
     *
     * @param  string  $message  Error message
     */
    private function logError( string $message ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[84EM Local Pages] Claude API Error: ' . $message );
        }

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            \WP_CLI::warning( $message );
        }
    }

    /**
     * Get usage statistics from the last API call
     *
     * @param  array  $response_data  Decoded API response
     *
     * @return array Usage statistics
     */
    public function getUsageStats( array $response_data ): array {
        if ( ! isset( $response_data['usage'] ) ) {
            return [
                'input_tokens'  => 0,
                'output_tokens' => 0,
                'total_tokens'  => 0,
            ];
        }

        return [
            'input_tokens'  => $response_data['usage']['input_tokens'] ?? 0,
            'output_tokens' => $response_data['usage']['output_tokens'] ?? 0,
            'total_tokens'  => ( $response_data['usage']['input_tokens'] ?? 0 ) + ( $response_data['usage']['output_tokens'] ?? 0 ),
        ];
    }
}
