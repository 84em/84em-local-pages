<?php
/**
 * Unit tests for Claude API Client
 *
 * @package EightyFourEM\LocalPages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

// Suppress expected warnings during API client tests
if ( ! defined( 'SUPPRESS_TEST_WARNINGS' ) ) {
    define( 'SUPPRESS_TEST_WARNINGS', true );
}

// Load autoloader for namespaced classes
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/TestCase.php';
require_once dirname( __DIR__ ) . '/test-config.php';

use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\Encryption;

class Test_API_Client extends TestCase {

    private ClaudeApiClient $apiClient;
    private ApiKeyManager $apiKeyManager;
    private Encryption $encryption;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp(); // Enables test mode (RUNNING_TESTS constant)

        // Create real service instances
        // These will automatically use test_ prefixed options due to RUNNING_TESTS
        $this->encryption = new Encryption();
        $this->apiKeyManager = new ApiKeyManager( $this->encryption );

        // Set test API key and model (will be stored in test_ prefixed options)
        $this->apiKeyManager->setKey( TestConfig::getTestApiKey() );
        $this->apiKeyManager->setModel( TestConfig::getTestModel() );

        $this->apiClient = new ClaudeApiClient( $this->apiKeyManager );
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        // Clean up test options using ApiKeyManager methods
        $this->apiKeyManager->deleteKey();
        $this->apiKeyManager->deleteModel();
    }
    
    /**
     * Test API client configuration check
     */
    public function test_is_configured() {
        // Test with valid API key and model (set in setUp)
        $this->assertTrue( $this->apiClient->isConfigured() );

        // Test with empty API key - delete keys first, then create new manager
        $this->apiKeyManager->deleteKey();
        $this->apiKeyManager->deleteModel();

        $emptyManager = new ApiKeyManager( $this->encryption );
        $emptyClient = new ClaudeApiClient( $emptyManager );
        $this->assertFalse( $emptyClient->isConfigured() );
    }
    
    /**
     * Test credential validation with real API calls
     */
    public function test_validate_credentials() {
        // Test with invalid key (empty) - delete keys first to ensure clean state
        $this->apiKeyManager->deleteKey();
        $this->apiKeyManager->deleteModel();

        $invalidManager = new ApiKeyManager( $this->encryption );
        $invalidClient = new ClaudeApiClient( $invalidManager );
        $this->assertFalse( $invalidClient->validateCredentials() );

        // Test with real production API key (makes real API call)
        // This test requires a valid API key to be configured in production
        $apiKey = TestConfig::getTestApiKey();
        if ( !empty( $apiKey ) ) {
            // Create a fresh manager with the API key
            $testManager = new ApiKeyManager( $this->encryption );
            $testManager->setKey( $apiKey );
            $testManager->setModel( TestConfig::getTestModel() );

            $validClient = new ClaudeApiClient( $testManager );
            $this->assertTrue( $validClient->validateCredentials() );
        }
    }
    
    
    /**
     * Test retryable error detection
     */
    public function test_is_retryable_error() {
        $reflection = new ReflectionClass( $this->apiClient );
        $method = $reflection->getMethod( 'isRetryableError' );
        $method->setAccessible( true );
        
        // Test retryable errors
        $retryableErrors = [
            'Connection timeout',
            'Request timed out',
            'Connection reset by peer',
            'Connection refused',
            'Could not resolve host',
            'Name or service not known',
            'Temporary failure in name resolution',
            'Network is unreachable'
        ];
        
        foreach ( $retryableErrors as $error ) {
            $this->assertTrue( 
                $method->invoke( $this->apiClient, $error ),
                "Failed to detect retryable error: {$error}"
            );
        }
        
        // Test non-retryable errors
        $nonRetryableErrors = [
            'Invalid API key',
            'Permission denied',
            'Bad request',
            'Invalid JSON'
        ];
        
        foreach ( $nonRetryableErrors as $error ) {
            $this->assertFalse( 
                $method->invoke( $this->apiClient, $error ),
                "Incorrectly detected as retryable: {$error}"
            );
        }
    }
    
    /**
     * Test retryable HTTP status codes
     */
    public function test_is_retryable_http_status() {
        $reflection = new ReflectionClass( $this->apiClient );
        $method = $reflection->getMethod( 'isRetryableHttpStatus' );
        $method->setAccessible( true );
        
        // Test retryable status codes
        $retryableCodes = [ 429, 500, 502, 503, 504, 507, 509 ];
        foreach ( $retryableCodes as $code ) {
            $this->assertTrue( 
                $method->invoke( $this->apiClient, $code ),
                "Failed to detect retryable status code: {$code}"
            );
        }
        
        // Test non-retryable status codes
        $nonRetryableCodes = [ 200, 201, 400, 401, 403, 404, 413, 422 ];
        foreach ( $nonRetryableCodes as $code ) {
            $this->assertFalse( 
                $method->invoke( $this->apiClient, $code ),
                "Incorrectly detected as retryable: {$code}"
            );
        }
    }
    
    /**
     * Test sendRequest with invalid configuration
     *
     * NOTE: This test will output a warning "API client is not properly configured"
     * which is expected behavior when testing error conditions.
     */
    public function test_send_request_invalid_config() {
        // Delete keys first to ensure clean state, then create new manager without any keys
        $this->apiKeyManager->deleteKey();
        $this->apiKeyManager->deleteModel();

        $invalidManager = new ApiKeyManager( $this->encryption );
        $invalidClient = new ClaudeApiClient( $invalidManager );

        $result = $invalidClient->sendRequest( 'Test prompt' );

        $this->assertFalse( $result );
    }
    
    
    /**
     * Test API key manager integration
     */
    public function test_api_key_manager_integration() {
        // Test that client uses the key manager
        $reflection = new ReflectionClass( $this->apiClient );
        $property = $reflection->getProperty( 'keyManager' );
        $property->setAccessible( true );

        $keyManager = $property->getValue( $this->apiClient );
        $this->assertInstanceOf( ApiKeyManager::class, $keyManager );
        $this->assertSame( $this->apiKeyManager, $keyManager );
    }
    
    
    /**
     * Test error scenarios in sendRequest
     *
     * NOTE: This test will output a warning "API client is not properly configured"
     * which is expected behavior when testing error conditions.
     */
    public function test_send_request_error_scenarios() {
        // Delete keys first to ensure clean state, then test with failed key retrieval (no key set)
        $this->apiKeyManager->deleteKey();
        $this->apiKeyManager->deleteModel();

        $failingManager = new ApiKeyManager( $this->encryption );
        $failingClient = new ClaudeApiClient( $failingManager );
        $result = $failingClient->sendRequest( 'Test prompt' );

        $this->assertFalse( $result );
    }
    
    
    /**
     * Test API error details logging
     */
    public function test_log_api_error_details() {
        $reflection = new ReflectionClass( $this->apiClient );
        $method = $reflection->getMethod( 'logApiErrorDetails' );
        $method->setAccessible( true );
        
        // Test with various status codes - should not throw exceptions
        $statusCodes = [ 400, 401, 403, 404, 413, 422, 500 ];
        
        foreach ( $statusCodes as $code ) {
            // Test with JSON error response
            $jsonError = json_encode( [
                'error' => [
                    'type' => 'invalid_request',
                    'message' => 'Test error message'
                ]
            ] );
            
            // This should not throw an exception
            $method->invoke( $this->apiClient, $code, $jsonError );
            
            // Test with plain text error
            $method->invoke( $this->apiClient, $code, 'Plain text error' );
            
            // Test with empty response
            $method->invoke( $this->apiClient, $code, '' );
        }
        
        // If we get here without exceptions, the test passes
        $this->assertTrue( true );
    }
}