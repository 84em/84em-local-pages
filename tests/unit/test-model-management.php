<?php
/**
 * Unit tests for API model management
 *
 * @package EightyFourEM_Local_Pages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Api\Encryption;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;

class Test_Model_Management extends TestCase {

	private Encryption $encryption;
	private ApiKeyManager $apiKeyManager;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp(); // Enables test mode (RUNNING_TESTS constant)

		// Create service instances
		// These will automatically use test_ prefixed options due to RUNNING_TESTS
		$this->encryption    = new Encryption();
		$this->apiKeyManager = new ApiKeyManager( $this->encryption );
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up test options using ApiKeyManager methods
		$this->apiKeyManager->deleteModel();
		$this->apiKeyManager->deleteKey();
	}

	/**
	 * Test model retrieval returns false when no model set
	 */
	public function test_get_model_returns_false_when_not_set() {
		// Ensure no custom model is set
		$this->apiKeyManager->deleteModel();

		$model = $this->apiKeyManager->getModel();
		$this->assertFalse( $model );
	}

	/**
	 * Test setting a custom model
	 */
	public function test_set_custom_model() {
		$custom_model = 'claude-opus-4-20250514';

		$result = $this->apiKeyManager->setModel( $custom_model );
		$this->assertTrue( $result );

		$retrieved_model = $this->apiKeyManager->getModel();
		$this->assertEquals( $custom_model, $retrieved_model );
	}

	/**
	 * Test deleting custom model returns false
	 */
	public function test_delete_model_returns_false() {
		// Set a custom model
		$this->apiKeyManager->setModel( 'claude-opus-4-20250514' );

		// Verify it was set
		$this->assertEquals( 'claude-opus-4-20250514', $this->apiKeyManager->getModel() );

		// Delete the custom model
		$result = $this->apiKeyManager->deleteModel();
		$this->assertTrue( $result );

		// Verify it now returns false
		$this->assertFalse( $this->apiKeyManager->getModel() );
	}

	/**
	 * Test hasCustomModel detection
	 */
	public function test_has_custom_model_detection() {
		// Ensure no custom model
		$this->apiKeyManager->deleteModel();

		$this->assertFalse( $this->apiKeyManager->hasCustomModel() );

		// Set a custom model
		$this->apiKeyManager->setModel( 'claude-opus-4-20250514' );

		$this->assertTrue( $this->apiKeyManager->hasCustomModel() );
	}

	/**
	 * Test hasKey method integration
	 */
	public function test_has_key_integration() {
		// Ensure no API key
		$this->apiKeyManager->deleteKey();

		$this->assertFalse( $this->apiKeyManager->hasKey() );

		// Set a test key
		$this->apiKeyManager->setKey( 'sk-ant-api03-test-key-for-has-key-test-12345678901234567890123456789012345678901234567890123456789012345678901234567' );

		$this->assertTrue( $this->apiKeyManager->hasKey() );
	}

	/**
	 * Test empty model name cannot be set
	 */
	public function test_empty_model_name_rejected() {
		$result = $this->apiKeyManager->setModel( '' );
		$this->assertFalse( $result );
	}

	/**
	 * Test validateModel without API key configured
	 */
	public function test_validate_model_without_api_key() {
		// Ensure no API key and no model are set
		$this->apiKeyManager->deleteKey();
		$this->apiKeyManager->deleteModel();

		$apiClient = new ClaudeApiClient( $this->apiKeyManager );
		$validation = $apiClient->validateModel( 'claude-sonnet-4-20250514' );

		$this->assertFalse( $validation['success'] );
		$this->assertStringContainsString( 'API key is not configured', $validation['message'] );
	}

	/**
	 * Test validateModel with empty model name
	 */
	public function test_validate_model_with_empty_name() {
		// Set both API key and a model so isConfigured() passes
		$this->apiKeyManager->setKey( 'sk-ant-api03-test-key-for-validation-12345678901234567890123456789012345678901234567890123456789012345678901234567' );
		$this->apiKeyManager->setModel( 'claude-sonnet-4-20250514' );

		$apiClient = new ClaudeApiClient( $this->apiKeyManager );

		// Now test with empty model name
		$validation = $apiClient->validateModel( '' );

		$this->assertFalse( $validation['success'] );
		$this->assertStringContainsString( 'cannot be empty', $validation['message'] );
	}

	/**
	 * Test model configuration persists across instances
	 */
	public function test_model_configuration_persists() {
		// Set a custom model
		$this->apiKeyManager->setModel( 'claude-opus-4-20250514' );

		// Create a new instance
		$newApiKeyManager = new ApiKeyManager( $this->encryption );

		// Verify the model persists
		$this->assertEquals( 'claude-opus-4-20250514', $newApiKeyManager->getModel() );
	}

	/**
	 * Test ClaudeApiClient uses model from ApiKeyManager
	 */
	public function test_api_client_uses_configured_model() {
		// This test verifies the integration between ApiKeyManager and ClaudeApiClient
		// We can't easily test the actual API call without mocking, but we can verify
		// that the model retrieval mechanism works

		$custom_model = 'claude-opus-4-20250514';
		$this->apiKeyManager->setModel( $custom_model );

		// Verify ApiKeyManager returns the custom model
		$this->assertEquals( $custom_model, $this->apiKeyManager->getModel() );

		// When no custom model is set, should return false
		$this->apiKeyManager->deleteModel();
		$this->assertFalse( $this->apiKeyManager->getModel() );
	}

	/**
	 * Test getAvailableModels without API key configured
	 */
	public function test_get_available_models_without_api_key() {
		// Ensure no API key is set
		$this->apiKeyManager->deleteKey();

		$apiClient = new ClaudeApiClient( $this->apiKeyManager );
		$result = $apiClient->getAvailableModels();

		$this->assertFalse( $result['success'] );
		$this->assertEmpty( $result['models'] );
		$this->assertStringContainsString( 'not properly configured', $result['message'] );
	}

	/**
	 * Test getAvailableModels returns expected structure
	 */
	public function test_get_available_models_structure() {
		// We can't test the actual API call without a real API key,
		// but we can verify the method exists and returns the correct structure
		// when API is not configured

		$apiClient = new ClaudeApiClient( $this->apiKeyManager );
		$result = $apiClient->getAvailableModels();

		// Verify the result has the expected keys
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'models', $result );
		$this->assertArrayHasKey( 'message', $result );

		// Verify types
		$this->assertIsBool( $result['success'] );
		$this->assertIsArray( $result['models'] );
		$this->assertIsString( $result['message'] );
	}

	/**
	 * Test isConfigured requires both API key and model
	 */
	public function test_is_configured_requires_both_key_and_model() {
		// Clear everything
		$this->apiKeyManager->deleteKey();
		$this->apiKeyManager->deleteModel();

		$apiClient = new ClaudeApiClient( $this->apiKeyManager );

		// Neither key nor model - not configured
		$this->assertFalse( $apiClient->isConfigured() );

		// Only API key - not configured
		$this->apiKeyManager->setKey( 'sk-ant-api03-test-key-for-validation-12345678901234567890123456789012345678901234567890123456789012345678901234567' );
		$this->assertFalse( $apiClient->isConfigured() );

		// Only model (no key) - not configured
		$this->apiKeyManager->deleteKey();
		$this->apiKeyManager->setModel( 'claude-sonnet-4-20250514' );
		$this->assertFalse( $apiClient->isConfigured() );

		// Both key and model - configured
		$this->apiKeyManager->setKey( 'sk-ant-api03-test-key-for-validation-12345678901234567890123456789012345678901234567890123456789012345678901234567' );
		$this->assertTrue( $apiClient->isConfigured() );
	}
}
