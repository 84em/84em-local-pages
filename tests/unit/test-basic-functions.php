<?php
/**
 * Basic functionality tests that don't require plugin instantiation
 *
 * @package EightyFourEM_Local_Pages
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

class Test_Basic_Functions extends TestCase {
    
    /**
     * Test that plugin file exists and is readable
     */
    public function test_plugin_file_exists() {
        $plugin_file = dirname( dirname( __DIR__ ) ) . '/84em-local-pages.php';
        $this->assertFileExists( $plugin_file );
    }
    
    /**
     * Test WordPress functions are available
     */
    public function test_wordpress_functions() {
        $this->assertTrue( function_exists( 'add_action' ) );
        $this->assertTrue( function_exists( 'add_filter' ) );
        $this->assertTrue( function_exists( 'get_option' ) );
        $this->assertTrue( function_exists( 'update_option' ) );
    }
    
    /**
     * Test WP-CLI is available
     */
    public function test_wp_cli_available() {
        $this->assertTrue( defined( 'WP_CLI' ) );
        $this->assertTrue( WP_CLI );
        $this->assertTrue( class_exists( 'WP_CLI' ) );
    }
    
    /**
     * Test plugin constants
     */
    public function test_plugin_constants() {
        $this->assertTrue( defined( 'ABSPATH' ) );
        $this->assertNotEmpty( ABSPATH );
    }
    
    /**
     * Test sanitization functions
     */
    public function test_sanitization() {
        $this->assertEquals( 'test-slug', sanitize_title( 'Test Slug' ) );
        $this->assertEquals( 'test123', sanitize_text_field( 'test123' ) );
        $this->assertEquals( 'test@example.com', sanitize_email( 'test@example.com' ) );
    }
    
    /**
     * Test URL generation
     */
    public function test_url_generation() {
        $home_url = home_url();
        $this->assertNotEmpty( $home_url );
        $this->assertStringStartsWith( 'http', $home_url );
        
        $admin_url = admin_url();
        $this->assertNotEmpty( $admin_url );
        $this->assertStringContainsString( 'wp-admin', $admin_url );
    }
}