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
        $this->markTestIncomplete( 'Success case requires real API key' );
    }
    
    /**
     * Test usage stats extraction
     */
    public function test_get_usage_stats() {
        $response_data = [
            'usage' => [
                'input_tokens' => 100,
                'output_tokens' => 500,
                'total_tokens' => 600
            ]
        ];
        
        $stats = $this->apiClient->getUsageStats( $response_data );
        
        $this->assertIsArray( $stats );
        $this->assertArrayHasKey( 'input_tokens', $stats );
        $this->assertArrayHasKey( 'output_tokens', $stats );
        $this->assertEquals( 100, $stats['input_tokens'] );
        $this->assertEquals( 500, $stats['output_tokens'] );
    }
    
    /**
     * Test usage stats with missing data
     */
    public function test_get_usage_stats_missing_data() {
        $response_data = [];
        
        $stats = $this->apiClient->getUsageStats( $response_data );
        
        $this->assertIsArray( $stats );
        $this->assertArrayHasKey( 'input_tokens', $stats );
        $this->assertArrayHasKey( 'output_tokens', $stats );
        $this->assertEquals( 0, $stats['input_tokens'] );
        $this->assertEquals( 0, $stats['output_tokens'] );
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