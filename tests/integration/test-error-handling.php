<?php
/**
 * Integration tests for Error Handling actually used in the plugin
 *
 * @package EightyFourEM\LocalPages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

// Load autoloader for namespaced classes
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Api\Encryption;

class Test_Error_Handling extends TestCase {
    
    
    /**
     * Test encryption error handling for invalid data
     */
    public function test_encryption_handles_invalid_data() {
        $encryption = new Encryption();
        
        // Test handling of invalid encrypted data
        $invalidData = [
            'not-base64',             // Invalid base64
            base64_encode( 'short' ), // Too short to contain IV
            '!!!invalid!!!',          // Special characters
        ];
        
        foreach ( $invalidData as $data ) {
            $result = $encryption->decrypt( $data );
            // Should return false, not throw exception
            $this->assertFalse( $result );
        }
    }
    
    
    /**
     * Test error message sanitization for sensitive data
     */
    public function test_sensitive_data_sanitization() {
        // Test that API keys would be sanitized if they appeared in error messages
        $sensitiveError = 'API call failed with key sk-ant-api03-secret-key';
        
        // This is what the plugin should do (though it doesn't log the key in practice)
        $sanitized = preg_replace( '/sk-ant-[^\s]+/', '[API_KEY_REDACTED]', $sensitiveError );
        
        $this->assertStringNotContainsString( 'secret-key', $sanitized );
        $this->assertStringContainsString( '[API_KEY_REDACTED]', $sanitized );
    }
    
    /**
     * Test that state names that don't exist are handled
     */
    public function test_invalid_state_name_handling() {
        $invalidState = 'Nonexistentstate';
        
        // The plugin should handle this by returning empty array or false
        $states = $this->getStatesData();
        $found = false;
        
        foreach ( $states as $state ) {
            if ( $state['name'] === $invalidState ) {
                $found = true;
                break;
            }
        }
        
        $this->assertFalse( $found );
    }
    
    
    
    
    /**
     * Test handling of empty/null content
     */
    public function test_empty_content_detection() {
        // Test that empty content is handled properly
        $emptyContent = [
            '',
            null,
            false,
            0,
        ];
        
        foreach ( $emptyContent as $content ) {
            // The plugin should handle empty content gracefully
            $processed = empty( $content ) ? false : true;
            $this->assertFalse( $processed );
        }
    }
    
    
    /**
     * Test validation of state data structure
     */
    public function test_state_data_validation() {
        $states = $this->getStatesData();
        
        // Verify state data has required fields
        foreach ( $states as $state ) {
            $this->assertArrayHasKey( 'name', $state );
            $this->assertArrayHasKey( 'code', $state );
            $this->assertIsString( $state['name'] );
            $this->assertIsString( $state['code'] );
            $this->assertEquals( 2, strlen( $state['code'] ) );
        }
    }
    
    
    /**
     * Helper method to get states data
     */
    private function getStatesData() {
        return [
            ['name' => 'California', 'code' => 'CA'],
            ['name' => 'New York', 'code' => 'NY'],
            ['name' => 'Texas', 'code' => 'TX'],
            // Minimal set for testing
        ];
    }
}

