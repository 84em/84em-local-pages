<?php
/**
 * Simple unit test that doesn't require WordPress
 *
 * @package EightyFourEM_Local_Pages
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

class Test_Simple extends TestCase {
    
    /**
     * Test basic functionality
     */
    public function test_php_version() {
        $this->assertTrue( version_compare( PHP_VERSION, '8.2', '>=' ) );
    }
    
    /**
     * Test that our main plugin file exists
     */
    public function test_plugin_file_exists() {
        $plugin_file = dirname( dirname( __DIR__ ) ) . '/84em-local-pages.php';
        $this->assertFileExists( $plugin_file );
    }
    
    /**
     * Test sanitize_title mock function
     */
    public function test_sanitize_title() {
        $this->assertEquals( 'hello-world', sanitize_title( 'Hello World' ) );
        $this->assertEquals( 'test-123', sanitize_title( 'Test 123!' ) );
        $this->assertEquals( 'multiple-spaces', sanitize_title( 'Multiple   Spaces' ) );
    }
    
    /**
     * Test URL functions
     */
    public function test_url_functions() {
        // When running in actual WordPress, home_url returns the actual site URL
        $home = home_url();
        $this->assertNotEmpty( $home );
        $this->assertStringStartsWith( 'http', $home );
        
        // Test that path is properly appended
        $test_url = home_url( '/test' );
        $this->assertStringEndsWith( '/test', $test_url );
        
        // Site URL should also be set
        $site = site_url();
        $this->assertNotEmpty( $site );
        $this->assertStringStartsWith( 'http', $site );
    }
    
    /**
     * Test escaping functions
     */
    public function test_escaping() {
        $this->assertEquals( 'https://example.com/test', esc_url( 'https://example.com/test' ) );
        $this->assertEquals( '&lt;script&gt;alert(1)&lt;/script&gt;', esc_html( '<script>alert(1)</script>' ) );
    }
}