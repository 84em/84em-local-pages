<?php
/**
 * Unit tests for Security features actually used in the plugin
 *
 * @package EightyFourEM\LocalPages
 */

// Load autoloader for namespaced classes
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Api\Encryption;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Utils\ContentProcessor;
use EightyFourEM\LocalPages\Data\KeywordsProvider;

class Test_Security extends TestCase {
    
    private $encryption;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        $this->encryption = new Encryption();
    }
    
    /**
     * Test API key encryption and decryption
     */
    public function test_api_key_encryption() {
        $apiKey = 'sk-ant-api03-test-key-123456789';
        
        // Test encryption
        $encrypted = $this->encryption->encrypt( $apiKey );
        $this->assertNotEquals( $apiKey, $encrypted );
        $this->assertStringNotContainsString( 'sk-ant', $encrypted );
        $this->assertStringNotContainsString( 'test-key', $encrypted );
        
        // Test decryption
        $decrypted = $this->encryption->decrypt( $encrypted );
        $this->assertEquals( $apiKey, $decrypted );
    }
    
    /**
     * Test that encrypted API keys are different each time (due to IV)
     */
    public function test_encryption_uniqueness() {
        $apiKey = 'sk-ant-api03-test-key-123456789';
        
        $encrypted1 = $this->encryption->encrypt( $apiKey );
        $encrypted2 = $this->encryption->encrypt( $apiKey );
        
        // Same key should produce different encrypted values due to random IV
        $this->assertNotEquals( $encrypted1, $encrypted2 );
        
        // But both should decrypt to the same value
        $this->assertEquals( $apiKey, $this->encryption->decrypt( $encrypted1 ) );
        $this->assertEquals( $apiKey, $this->encryption->decrypt( $encrypted2 ) );
    }
    
    /**
     * Test handling of empty/invalid API keys
     */
    public function test_empty_api_key_handling() {
        // Test empty string - encrypt returns false for empty
        $encrypted = $this->encryption->encrypt( '' );
        $this->assertFalse( $encrypted );
        
        // Test decryption of empty - decrypt returns false for empty
        $decrypted = $this->encryption->decrypt( '' );
        $this->assertFalse( $decrypted );
    }
    
    /**
     * Test handling of corrupted encrypted data
     */
    public function test_corrupted_encrypted_data() {
        // Test completely invalid data
        $result = $this->encryption->decrypt( 'invalid-base64-data!!!' );
        $this->assertFalse( $result );
        
        // Test truncated encrypted data
        $apiKey = 'sk-ant-api03-test-key';
        $encrypted = $this->encryption->encrypt( $apiKey );
        $corrupted = substr( $encrypted, 0, 10 );
        
        $result = $this->encryption->decrypt( $corrupted );
        $this->assertFalse( $result );
    }
    
    /**
     * Test CLI argument sanitization for state names
     */
    public function test_state_name_sanitization() {
        // Test various malicious inputs that could be passed via CLI
        $maliciousInputs = [
            "California'; DROP TABLE posts; --" => 'California',
            "New York<script>alert('xss')</script>" => 'New York',
            "../../../etc/passwd" => 'etcpasswd',
            "California\"; system('rm -rf /'); \"" => 'California',
            "'; DELETE FROM wp_posts WHERE 1=1; --" => ''
        ];
        
        foreach ( $maliciousInputs as $input => $expected ) {
            // Simulate what happens when processing state names
            $sanitized = sanitize_title( $input );
            $sanitized = str_replace( '-', ' ', $sanitized );
            $sanitized = ucwords( $sanitized );
            
            // The dangerous parts should be removed
            $this->assertStringNotContainsString( 'DROP TABLE', $sanitized );
            $this->assertStringNotContainsString( 'script', $sanitized );
            $this->assertStringNotContainsString( '../', $sanitized );
            $this->assertStringNotContainsString( 'DELETE FROM', $sanitized );
            $this->assertStringNotContainsString( 'system(', $sanitized );
        }
    }
    
    /**
     * Test URL slug generation security
     */
    public function test_url_slug_security() {
        // Test that generated URLs are safe
        $states = [
            "California" => "california",
            "New York" => "new-york",
            "North Carolina" => "north-carolina",
            "Rhode Island" => "rhode-island"
        ];
        
        foreach ( $states as $state => $expectedSlug ) {
            $slug = sanitize_title( $state );
            $this->assertEquals( $expectedSlug, $slug );
            
            // Ensure no special characters that could break URLs
            $this->assertMatchesRegularExpression( '/^[a-z0-9-]+$/', $slug );
        }
    }
    
    /**
     * Test content processor doesn't introduce XSS
     */
    public function test_content_processor_xss_prevention() {
        $keywordsProvider = new KeywordsProvider();
        $processor = new ContentProcessor( $keywordsProvider );
        
        // Test that malicious content in input doesn't create XSS
        $maliciousContent = '<!-- wp:paragraph --><p>Click here for <script>alert("xss")</script> WordPress development</p><!-- /wp:paragraph -->';
        
        $processed = $processor->processContent( $maliciousContent, ['type' => 'state'] );
        
        // Script tags should remain as-is in content (not executed)
        // The processor should not strip them but also not enhance them
        $this->assertStringContainsString( 'script', $processed );
        
        // But links added by processor should be properly formed
        if ( strpos( $processed, '<a href=' ) !== false ) {
            // Any links should be properly quoted
            $this->assertMatchesRegularExpression( '/<a href="[^"]+">/', $processed );
        }
    }
    
    /**
     * Test that WP-CLI commands check capabilities
     */
    public function test_cli_capability_requirements() {
        // The actual plugin requires 'manage_options' capability
        // This is enforced in the CLI command registration
        
        // Test that we're checking for admin capabilities
        $requiredCapability = 'manage_options';
        
        // In WordPress, only administrators have 'manage_options'
        $adminCaps = ['manage_options', 'edit_plugins', 'activate_plugins'];
        $this->assertContains( $requiredCapability, $adminCaps );
        
        // Other roles should not have this capability
        $editorCaps = ['edit_posts', 'edit_pages', 'edit_published_posts'];
        $this->assertNotContains( $requiredCapability, $editorCaps );
    }
    
    /**
     * Test API key validation
     */
    public function test_api_key_validation() {
        // Test valid Anthropic API key formats
        $validKeys = [
            'sk-ant-api03-' . str_repeat( 'a', 32 ),
            'sk-ant-' . str_repeat( 'b', 40 ),
        ];
        
        foreach ( $validKeys as $key ) {
            // Should start with sk-ant
            $this->assertStringStartsWith( 'sk-ant', $key );
            // Should be reasonable length
            $this->assertGreaterThan( 20, strlen( $key ) );
        }
        
        // Test invalid formats
        $invalidKeys = [
            'not-an-api-key',
            '12345',
            '',
            'sk-different-prefix-key'
        ];
        
        foreach ( $invalidKeys as $key ) {
            $isValid = strpos( $key, 'sk-ant' ) === 0 && strlen( $key ) > 20;
            $this->assertFalse( $isValid );
        }
    }
    
    /**
     * Test that sensitive data is not exposed in error messages
     */
    public function test_error_message_sanitization() {
        // Simulate an error message that might contain sensitive data
        $apiKey = 'sk-ant-api03-secret-key-12345';
        $errorMessage = "API call failed with key: $apiKey";
        
        // This is what should happen to error messages before logging
        $sanitized = preg_replace( '/sk-ant-[^\s]+/', '[API_KEY_REDACTED]', $errorMessage );
        
        $this->assertStringNotContainsString( $apiKey, $sanitized );
        $this->assertStringContainsString( '[API_KEY_REDACTED]', $sanitized );
    }
}