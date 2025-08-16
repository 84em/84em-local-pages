<?php
/**
 * Unit tests for Claude API Client
 *
 * @package EightyFourEM\LocalPages
 */

// Suppress expected warnings during API client tests
if ( ! defined( 'SUPPRESS_TEST_WARNINGS' ) ) {
    define( 'SUPPRESS_TEST_WARNINGS', true );
}

// Load autoloader for namespaced classes
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Api\ApiKeyManager;

class Test_API_Client extends TestCase {
    
    private $apiClient;
    private $mockKeyManager;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        // Create mock API key manager
        $this->mockKeyManager = $this->createMockApiKeyManager();
        $this->apiClient = new ClaudeApiClient( $this->mockKeyManager );
    }
    
    /**
     * Test API client configuration check
     */
    public function test_is_configured() {
        // Test with valid API key
        $this->assertTrue( $this->apiClient->isConfigured() );
        
        // Test with empty API key
        $emptyManager = $this->createMockApiKeyManager( '' );
        $emptyClient = new ClaudeApiClient( $emptyManager );
        $this->assertFalse( $emptyClient->isConfigured() );
    }
    
    /**
     * Test credential validation
     */
    public function test_validate_credentials() {
        // Note: validateCredentials makes a real API call, so we can only test the failure case
        
        // With invalid key (empty)
        $invalidManager = $this->createMockApiKeyManager( '' );
        $invalidClient = new ClaudeApiClient( $invalidManager );
        $this->assertFalse( $invalidClient->validateCredentials() );
        
        // The success case would require a real API key and actual API call,
        // which we can't test in unit tests
        // Note: Skipping success case as it requires real API key
    }
    
    /**
     * Test retry logic constants
     */
    public function test_retry_constants() {
        $reflection = new ReflectionClass( $this->apiClient );
        
        // Check max retries
        $maxRetries = $reflection->getConstant( 'MAX_RETRIES' );
        $this->assertIsInt( $maxRetries );
        $this->assertGreaterThan( 0, $maxRetries );
        $this->assertLessThanOrEqual( 5, $maxRetries );
        
        // Check initial retry delay
        $initialDelay = $reflection->getConstant( 'INITIAL_RETRY_DELAY' );
        $this->assertIsInt( $initialDelay );
        $this->assertGreaterThan( 0, $initialDelay );
        
        // Check max retry delay
        $maxDelay = $reflection->getConstant( 'MAX_RETRY_DELAY' );
        $this->assertIsInt( $maxDelay );
        $this->assertGreaterThan( $initialDelay, $maxDelay );
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
        $invalidManager = $this->createMockApiKeyManager( '' );
        $invalidClient = new ClaudeApiClient( $invalidManager );
        
        $result = $invalidClient->sendRequest( 'Test prompt' );
        
        $this->assertFalse( $result );
    }
    
    /**
     * Test API constants
     */
    public function test_api_constants() {
        $reflection = new ReflectionClass( $this->apiClient );
        
        // Check API endpoint
        $endpoint = $reflection->getConstant( 'API_ENDPOINT' );
        $this->assertStringContainsString( 'anthropic.com', $endpoint );
        $this->assertStringContainsString( '/messages', $endpoint );
        
        // Check API version
        $version = $reflection->getConstant( 'API_VERSION' );
        $this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', $version );
        
        // Check model
        $model = $reflection->getConstant( 'MODEL' );
        $this->assertStringContainsString( 'claude', $model );
        
        // Check max tokens
        $maxTokens = $reflection->getConstant( 'MAX_TOKENS' );
        $this->assertIsInt( $maxTokens );
        $this->assertGreaterThan( 1000, $maxTokens );
        
        // Check timeout
        $timeout = $reflection->getConstant( 'TIMEOUT' );
        $this->assertIsInt( $timeout );
        $this->assertGreaterThanOrEqual( 30, $timeout );
        
        // Check retry constants
        $maxRetries = $reflection->getConstant( 'MAX_RETRIES' );
        $this->assertIsInt( $maxRetries );
        $this->assertEquals( 3, $maxRetries );
        
        $initialDelay = $reflection->getConstant( 'INITIAL_RETRY_DELAY' );
        $this->assertIsInt( $initialDelay );
        $this->assertEquals( 1, $initialDelay );
        
        $maxDelay = $reflection->getConstant( 'MAX_RETRY_DELAY' );
        $this->assertIsInt( $maxDelay );
        $this->assertEquals( 30, $maxDelay );
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
        $this->assertSame( $this->mockKeyManager, $keyManager );
    }
    
    /**
     * Test with different API key formats
     */
    public function test_different_api_key_formats() {
        // Test with Anthropic format key
        $anthropicKey = 'sk-ant-api03-test-key';
        $manager1 = $this->createMockApiKeyManager( $anthropicKey );
        $client1 = new ClaudeApiClient( $manager1 );
        $this->assertTrue( $client1->isConfigured() );
        
        // Test with regular string key
        $regularKey = 'regular-api-key-123';
        $manager2 = $this->createMockApiKeyManager( $regularKey );
        $client2 = new ClaudeApiClient( $manager2 );
        $this->assertTrue( $client2->isConfigured() );
        
        // Test with null key
        $nullManager = $this->createMockApiKeyManager( null );
        $nullClient = new ClaudeApiClient( $nullManager );
        $this->assertFalse( $nullClient->isConfigured() );
    }
    
    /**
     * Test error scenarios in sendRequest
     * 
     * NOTE: This test will output a warning "API client is not properly configured"
     * which is expected behavior when testing error conditions.
     */
    public function test_send_request_error_scenarios() {
        // Test with failed key retrieval
        $failingManager = new class extends ApiKeyManager {
            public function __construct() {
                // Don't call parent to avoid database dependencies
            }
            
            public function getKey(): string|false {
                return false; // Simulate key retrieval failure
            }
            
            public function hasKey(): bool {
                return false; // Key retrieval fails, so no key
            }
            
            public function getApiKey(): ?string {
                return 'test-key';
            }
            
            public function setApiKey( string $apiKey ): bool {
                return true;
            }
            
            public function validateApiKey(): bool {
                return true;
            }
        };
        
        $failingClient = new ClaudeApiClient( $failingManager );
        $result = $failingClient->sendRequest( 'Test prompt' );
        
        $this->assertFalse( $result );
    }
    
    /**
     * Test logging methods accessibility
     */
    public function test_logging_methods() {
        $reflection = new ReflectionClass( $this->apiClient );
        
        // Check that logging methods exist
        $this->assertTrue( $reflection->hasMethod( 'logError' ) );
        $this->assertTrue( $reflection->hasMethod( 'logWarning' ) );
        $this->assertTrue( $reflection->hasMethod( 'logInfo' ) );
        $this->assertTrue( $reflection->hasMethod( 'logApiErrorDetails' ) );
        
        // Check that they are private (encapsulation)
        $logError = $reflection->getMethod( 'logError' );
        $this->assertTrue( $logError->isPrivate() );
        
        $logWarning = $reflection->getMethod( 'logWarning' );
        $this->assertTrue( $logWarning->isPrivate() );
        
        $logInfo = $reflection->getMethod( 'logInfo' );
        $this->assertTrue( $logInfo->isPrivate() );
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
    
    /**
     * Helper to create mock API key manager
     */
    private function createMockApiKeyManager( $apiKey = 'test-api-key' ) {
        return new class( $apiKey ) extends ApiKeyManager {
            private $key;
            
            public function __construct( $key ) {
                $this->key = $key;
                // Don't call parent constructor to avoid database dependencies
            }
            
            public function getKey(): string|false {
                return $this->key ?: false;
            }
            
            public function hasKey(): bool {
                return !empty( $this->key );
            }
            
            public function getApiKey(): ?string {
                return $this->key;
            }
            
            public function setApiKey( string $apiKey ): bool {
                $this->key = $apiKey;
                return true;
            }
            
            public function validateApiKey(): bool {
                return !empty( $this->key );
            }
        };
    }
}