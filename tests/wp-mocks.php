<?php
/**
 * WordPress function mocks for testing
 *
 * @package EightyFourEM_Local_Pages
 */

// Define WordPress constants if not already defined
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/home/andrew/workspace/84em/app/public/' );
}

// Mock WordPress functions needed for tests
if ( ! function_exists( 'sanitize_title' ) ) {
    function sanitize_title( $title ) {
        $title = strtolower( $title );
        $title = preg_replace( '/[^a-z0-9\s-]/', '', $title );
        $title = preg_replace( '/[\s]+/', '-', $title );
        return trim( $title, '-' );
    }
}

if ( ! function_exists( 'home_url' ) ) {
    function home_url( $path = '' ) {
        return 'https://84em.com' . $path;
    }
}

if ( ! function_exists( 'site_url' ) ) {
    function site_url( $path = '' ) {
        return 'https://84em.com' . $path;
    }
}

if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) {
        return $url; // Simplified for testing
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        global $wp_test_options;
        if ( ! isset( $wp_test_options ) ) {
            $wp_test_options = [];
        }
        return isset( $wp_test_options[$option] ) ? $wp_test_options[$option] : $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value ) {
        global $wp_test_options;
        if ( ! isset( $wp_test_options ) ) {
            $wp_test_options = [];
        }
        $wp_test_options[$option] = $value;
        return true;
    }
}

if ( ! function_exists( 'delete_option' ) ) {
    function delete_option( $option ) {
        global $wp_test_options;
        if ( ! isset( $wp_test_options ) ) {
            $wp_test_options = [];
        }
        unset( $wp_test_options[$option] );
        return true;
    }
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path( $file ) {
        return dirname( $file ) . '/';
    }
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
    function wp_verify_nonce( $nonce, $action = -1 ) {
        return true; // Simplified for testing
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
        // Mock implementation
        return true;
    }
}

if ( ! function_exists( 'remove_all_filters' ) ) {
    function remove_all_filters( $tag, $priority = false ) {
        // Mock implementation
        return true;
    }
}

// Mock WordPress classes
if ( ! class_exists( 'WP_CLI' ) ) {
    class WP_CLI {
        public static function log( $message ) {
            echo "[LOG] $message\n";
        }
        
        public static function warning( $message ) {
            echo "[WARNING] $message\n";
        }
        
        public static function error( $message, $exit = true ) {
            echo "[ERROR] $message\n";
            if ( $exit ) {
                throw new Exception( $message );
            }
        }
        
        public static function success( $message ) {
            echo "[SUCCESS] $message\n";
        }
        
        public static function line( $message = '' ) {
            echo "$message\n";
        }
    }
}

// Create Mock_Claude_API class for API response mocking
class Mock_Claude_API {
    public static function success_response( $content = null ) {
        if ( ! $content ) {
            $content = self::get_default_content();
        }
        
        return [
            'response' => ['code' => 200],
            'body' => json_encode( [
                'content' => [
                    ['text' => $content]
                ]
            ] )
        ];
    }
    
    public static function error_response( $code = 500, $message = 'API Error' ) {
        return [
            'response' => ['code' => $code],
            'body' => json_encode( [
                'error' => ['message' => $message]
            ] )
        ];
    }
    
    private static function get_default_content() {
        return '<!-- wp:paragraph --><p>Test content for California.</p><!-- /wp:paragraph -->';
    }
}