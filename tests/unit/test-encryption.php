<?php
/**
 * Unit tests for encryption and API key management
 *
 * @package EightyFourEM_Local_Pages
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Api\Encryption;
use EightyFourEM\LocalPages\Api\ApiKeyManager;

class Test_Encryption extends TestCase {

    private Encryption $encryption;
    private ApiKeyManager $apiKeyManager;
    private $original_option_value;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        // Store original option value
        $this->original_option_value = get_option( '84em_claude_api_key_encrypted' );

        // Create service instances
        $this->encryption    = new Encryption();
        $this->apiKeyManager = new ApiKeyManager( $this->encryption );
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        // Restore original option value
        if ( $this->original_option_value !== false ) {
            update_option( '84em_claude_api_key_encrypted', $this->original_option_value );
        } else {
            delete_option( '84em_claude_api_key_encrypted' );
        }

        // Clean up IV option that might have been created
        delete_option( '84em_claude_api_key_iv' );
    }

    /**
     * Test API key encryption and decryption
     */
    public function test_api_key_encryption_decryption() {
        // Test with a sample API key
        $test_key = 'sk-ant-api03-test-key-12345';

        // Set the key
        $result = $this->apiKeyManager->setKey( $test_key );
        $this->assertTrue( $result, 'Failed to set API key' );

        // Retrieve and verify
        $retrieved_key = $this->apiKeyManager->getKey();
        $this->assertEquals( $test_key, $retrieved_key );
    }

    /**
     * Test that API key is not stored in plain text
     */
    public function test_api_key_not_stored_plain_text() {
        $test_key = 'sk-ant-api03-test-key-12345';
        $this->apiKeyManager->setKey( $test_key );

        $stored = get_option( '84em_claude_api_key_encrypted' );
        $stored_iv = get_option( '84em_claude_api_key_iv' );

        // Verify it's encrypted
        $this->assertNotEmpty( $stored );
        $this->assertNotEmpty( $stored_iv );

        // Verify it's not plain text
        $decrypted = base64_decode( $stored );
        $this->assertNotEquals( $test_key, $decrypted );
        $this->assertStringNotContainsString( 'sk-ant', $decrypted );
    }

    /**
     * Test encryption key generation consistency
     */
    public function test_encryption_key_generation() {
        // Create two encryption instances
        $encryption1 = new Encryption();
        $encryption2 = new Encryption();

        // Test that both can encrypt and decrypt consistently
        $test_data = 'test-data-' . time();
        $encrypted = $encryption1->encrypt( $test_data );
        $decrypted = $encryption2->decrypt( $encrypted );

        $this->assertEquals( $test_data, $decrypted );
    }

    /**
     * Test handling of empty/missing API key
     */
    public function test_empty_api_key_handling() {
        // Ensure no key is stored
        delete_option( '84em_claude_api_key_encrypted' );
        delete_option( '84em_claude_api_key_iv' );

        $result = $this->apiKeyManager->getKey();
        $this->assertFalse( $result );

        // Test hasKey method
        $this->assertFalse( $this->apiKeyManager->hasKey() );
    }

    /**
     * Test handling of corrupted encrypted data
     */
    public function test_corrupted_data_handling() {
        // Set corrupted data
        update_option( '84em_claude_api_key_encrypted', 'corrupted-data' );

        $result = $this->apiKeyManager->getKey();
        $this->assertFalse( $result );
    }

    /**
     * Test validate API key format
     */
    public function test_validate_claude_api_key() {
        // Valid format
        $valid_key = 'sk-ant-api03-' . str_repeat( 'a', 93 );
        $this->assertTrue( $this->apiKeyManager->validateKeyFormat( $valid_key ) );

        // Invalid formats
        $this->assertFalse( $this->apiKeyManager->validateKeyFormat( 'invalid-key' ) );
        $this->assertFalse( $this->apiKeyManager->validateKeyFormat( 'sk-ant-' ) );
        $this->assertFalse( $this->apiKeyManager->validateKeyFormat( '' ) );
    }

    /**
     * Test stored API key validation
     */
    public function test_validate_stored_api_key() {
        // Store a valid key
        $valid_key = 'sk-ant-api03-' . str_repeat( 'x', 93 );
        $this->apiKeyManager->setKey( $valid_key );

        // Should validate successfully
        $this->assertTrue( $this->apiKeyManager->validateStoredKey() );

        // Delete key
        $this->apiKeyManager->deleteKey();

        // Should fail validation when no key exists
        $this->assertFalse( $this->apiKeyManager->validateStoredKey() );
    }
}
