<?php
/**
 * Integration tests for encryption and API key management
 *
 * @package EightyFourEM_Local_Pages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Api\Encryption;
use EightyFourEM\LocalPages\Api\ApiKeyManager;

class Test_Encryption extends TestCase {

    private Encryption $encryption;
    private ApiKeyManager $apiKeyManager;
    private $original_key_value;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        // Create service instances
        $this->encryption    = new Encryption();
        $this->apiKeyManager = new ApiKeyManager( $this->encryption );

        // Store original key value
        $this->original_key_value = $this->apiKeyManager->getKey();
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        // Restore original key value or delete if none existed
        if ( $this->original_key_value !== false ) {
            $this->apiKeyManager->setKey( $this->original_key_value );
        } else {
            $this->apiKeyManager->deleteKey();
        }
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
     * Test that encryption round-trip works correctly
     */
    public function test_encryption_round_trip() {
        $test_key = 'sk-ant-api03-test-key-for-encryption-test-1234567890123456789012345678901234567890123456789012345678901234567890';

        // Set the key
        $result = $this->apiKeyManager->setKey( $test_key );
        $this->assertTrue( $result, 'setKey should return true' );

        // Retrieve the key and verify it matches
        $retrieved_key = $this->apiKeyManager->getKey();
        $this->assertEquals( $test_key, $retrieved_key, 'Retrieved key should match original' );

        // Delete and verify it's gone
        $this->apiKeyManager->deleteKey();
        $this->assertFalse( $this->apiKeyManager->getKey(), 'Key should be false after deletion' );
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
