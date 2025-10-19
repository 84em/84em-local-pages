<?php
/**
 * Mock Claude API responses for testing
 *
 * @package EightyFourEM_Local_Pages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

class Mock_Claude_API {
    
    /**
     * Generate a successful API response
     *
     * @param string|null $content Optional content to return
     * @return array WordPress HTTP response array
     */
    public static function success_response( $content = null ) {
        if ( ! $content ) {
            $content = self::get_default_state_content();
        }
        
        return [
            'response' => [
                'code' => 200,
                'message' => 'OK'
            ],
            'headers' => [
                'content-type' => 'application/json'
            ],
            'body' => json_encode( [
                'id' => 'msg_' . uniqid(),
                'type' => 'message',
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $content
                    ]
                ]
            ] ),
            'cookies' => [],
            'filename' => null
        ];
    }
    
    /**
     * Generate an error API response
     *
     * @param int $code HTTP status code
     * @param string $message Error message
     * @return array WordPress HTTP response array
     */
    public static function error_response( $code = 500, $message = 'Internal Server Error' ) {
        return [
            'response' => [
                'code' => $code,
                'message' => $message
            ],
            'headers' => [
                'content-type' => 'application/json'
            ],
            'body' => json_encode( [
                'error' => [
                    'type' => 'api_error',
                    'message' => $message
                ]
            ] ),
            'cookies' => [],
            'filename' => null
        ];
    }
    
    /**
     * Generate a rate limit error response
     *
     * @return array WordPress HTTP response array
     */
    public static function rate_limit_response() {
        return self::error_response( 429, 'Rate limit exceeded' );
    }
    
    /**
     * Get default state page content
     *
     * @return string
     */
    private static function get_default_state_content() {
        return '<!-- wp:paragraph -->
<p>Looking for expert WordPress development services in California? 84EM delivers professional WordPress solutions to businesses throughout the Golden State, including Los Angeles, San Francisco, San Diego, San Jose, Sacramento, and Fresno.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2><strong>WordPress Development Services for California Businesses</strong></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Our comprehensive WordPress development services include custom plugin development, API integrations, and WordPress maintenance tailored to meet the unique needs of California businesses.</p>
<!-- /wp:paragraph -->';
    }
    
    /**
     * Get default city page content
     *
     * @param string $city City name
     * @param string $state State name
     * @return string
     */
    public static function get_city_content( $city, $state ) {
        return "<!-- wp:paragraph -->
<p>Looking for expert WordPress development services in {$city}, {$state}? 84EM delivers professional WordPress solutions to businesses in {$city}.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {\"level\":2} -->
<h2><strong>WordPress Development Services in {$city}</strong></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Our team provides custom WordPress development, plugin development, and ongoing support to help {$city} businesses succeed online.</p>
<!-- /wp:paragraph -->";
    }
    
    /**
     * Mock HTTP request filter
     *
     * @param false|array $preempt Whether to preempt the request
     * @param array $args Request arguments
     * @param string $url Request URL
     * @return false|array
     */
    public static function mock_http_request( $preempt, $args, $url ) {
        if ( strpos( $url, 'api.anthropic.com' ) !== false ) {
            // Check for API key
            if ( empty( $args['headers']['x-api-key'] ) ) {
                return self::error_response( 401, 'Unauthorized' );
            }
            
            // Parse the request body
            $body = json_decode( $args['body'], true );
            
            if ( ! empty( $body['messages'][0]['content'] ) ) {
                $prompt = $body['messages'][0]['content'];
                
                // Return content based on prompt
                if ( strpos( $prompt, 'California' ) !== false ) {
                    return self::success_response();
                } elseif ( preg_match( '/in (\w+), (\w+)/', $prompt, $matches ) ) {
                    return self::success_response( self::get_city_content( $matches[1], $matches[2] ) );
                }
            }
            
            return self::success_response();
        }
        
        return $preempt;
    }
    
    /**
     * Enable API mocking
     */
    public static function enable() {
        add_filter( 'pre_http_request', [ __CLASS__, 'mock_http_request' ], 10, 3 );
    }
    
    /**
     * Disable API mocking
     */
    public static function disable() {
        remove_filter( 'pre_http_request', [ __CLASS__, 'mock_http_request' ], 10 );
    }
}