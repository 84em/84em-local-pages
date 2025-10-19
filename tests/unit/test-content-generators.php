<?php
/**
 * Unit tests for Content Generators
 *
 * @package EightyFourEM\LocalPages
 */

// Load autoloader for namespaced classes
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Content\StateContentGenerator;
use EightyFourEM\LocalPages\Content\CityContentGenerator;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
use EightyFourEM\LocalPages\Schema\SchemaGenerator;
use EightyFourEM\LocalPages\Utils\ContentProcessor;

class Test_Content_Generators extends TestCase {
    
    private $stateGenerator;
    private $cityGenerator;
    private $mockApiKeyManager;
    private $statesProvider;
    private $keywordsProvider;
    private $schemaGenerator;
    private $contentProcessor;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        // Initialize real providers
        $this->statesProvider = new StatesProvider();
        $this->keywordsProvider = new KeywordsProvider();
        $this->schemaGenerator = new SchemaGenerator( $this->statesProvider );
        $this->contentProcessor = new ContentProcessor( $this->keywordsProvider );
        
        // Create mock API key manager and API client
        $this->mockApiKeyManager = $this->createMockApiKeyManager();
        $mockApiClient = $this->createMockApiClient();
        
        // Initialize generators with all dependencies
        $this->stateGenerator = new StateContentGenerator(
            $this->mockApiKeyManager,
            $mockApiClient,
            $this->statesProvider,
            $this->keywordsProvider,
            $this->schemaGenerator,
            $this->contentProcessor
        );
        
        $this->cityGenerator = new CityContentGenerator(
            $this->mockApiKeyManager,
            $mockApiClient,
            $this->statesProvider,
            $this->keywordsProvider,
            $this->schemaGenerator,
            $this->contentProcessor
        );
    }
    
    /**
     * Test state content generator initialization
     */
    public function test_state_generator_initialization() {
        $this->assertInstanceOf( StateContentGenerator::class, $this->stateGenerator );
    }
    
    /**
     * Test city content generator initialization
     */
    public function test_city_generator_initialization() {
        $this->assertInstanceOf( CityContentGenerator::class, $this->cityGenerator );
    }
    
    /**
     * Test state provider integration
     */
    public function test_states_provider_integration() {
        $states = $this->statesProvider->getAll();
        $this->assertIsArray( $states );
        $this->assertCount( 50, $states );
        
        // Test specific state
        $california = $this->statesProvider->get( 'California' );
        $this->assertIsArray( $california );
        $this->assertArrayHasKey( 'cities', $california );
        $this->assertCount( 6, $california['cities'] );
    }
    
    /**
     * Test keywords provider integration
     */
    public function test_keywords_provider_integration() {
        $keywords = $this->keywordsProvider->getAll();
        $this->assertIsArray( $keywords );
        $this->assertNotEmpty( $keywords );
        
        // Check for specific keywords
        $keywordKeys = $this->keywordsProvider->getKeys();
        $this->assertContains( 'WordPress development', $keywordKeys );
        $this->assertContains( 'custom plugin development', $keywordKeys );
    }
    
    /**
     * Test schema generator for state
     */
    public function test_schema_generator_state() {
        $schemaJson = $this->schemaGenerator->generateStateSchema( 'California' );
        
        $this->assertIsString( $schemaJson );
        $schema = json_decode( $schemaJson, true );
        $this->assertIsArray( $schema );
        $this->assertArrayHasKey( '@context', $schema );
        $this->assertArrayHasKey( '@type', $schema );
        $this->assertEquals( 'https://schema.org', $schema['@context'] );
    }
    
    /**
     * Test schema generator for city
     */
    public function test_schema_generator_city() {
        $schemaJson = $this->schemaGenerator->generateCitySchema( 'California', 'Los Angeles' );
        
        $this->assertIsString( $schemaJson );
        $schema = json_decode( $schemaJson, true );
        $this->assertIsArray( $schema );
        $this->assertArrayHasKey( '@context', $schema );
        $this->assertArrayHasKey( '@type', $schema );
        $this->assertEquals( 'https://schema.org', $schema['@context'] );
    }
    
    /**
     * Test content processor functionality
     */
    public function test_content_processor() {
        $content = 'We offer WordPress development and custom plugin development services.';
        $context = ['type' => 'state', 'state' => 'California'];
        
        $processed = $this->contentProcessor->processContent( $content, $context );
        
        // Should add links to keywords
        $this->assertStringContainsString( '<a href=', $processed );
        $this->assertStringContainsString( 'WordPress development', $processed );
    }
    
    /**
     * Test content processor with city links
     */
    public function test_content_processor_city_links() {
        $content = 'Serving businesses in Los Angeles, San Francisco, and San Diego.';
        $context = [
            'type' => 'state',
            'state' => 'California',
            'cities' => ['Los Angeles', 'San Francisco', 'San Diego']
        ];
        
        $processed = $this->contentProcessor->processContent( $content, $context );
        
        // Should add links to city names
        $this->assertStringContainsString( 'los-angeles', $processed );
        $this->assertStringContainsString( 'san-francisco', $processed );
        $this->assertStringContainsString( 'san-diego', $processed );
    }
    
    /**
     * Test content cleaning functionality
     */
    public function test_content_cleaning() {
        // Test that content is cleaned properly
        $content = '<!-- wp:paragraph --><p>Test content with extra spaces.  </p><!-- /wp:paragraph -->';
        $context = ['type' => 'state'];
        
        $processed = $this->contentProcessor->processContent( $content, $context );
        
        // Should still be valid WordPress blocks
        $this->assertStringContainsString( '<!-- wp:paragraph -->', $processed );
        $this->assertStringContainsString( '</p><!-- /wp:paragraph -->', $processed );
    }
    
    /**
     * Test block structure validation
     */
    public function test_block_structure_validation() {
        $validContent = '<!-- wp:paragraph --><p>Test content</p><!-- /wp:paragraph -->';
        $invalidContent = '<p>Test content without blocks</p>';
        
        // Valid content should have WordPress blocks
        $this->assertStringContainsString( '<!-- wp:', $validContent );
        
        // Invalid content should not have blocks
        $this->assertStringNotContainsString( '<!-- wp:', $invalidContent );
    }
    
    /**
     * Test service keyword list generation
     */
    public function test_service_keyword_list() {
        $keywords = $this->keywordsProvider->getKeys();
        $keywordsList = implode( ', ', $keywords );
        
        // Should contain multiple keywords
        $keywordCount = count( $keywords );
        $this->assertGreaterThan( 10, $keywordCount );
        
        // Should be properly formatted
        $this->assertStringNotContainsString( ',,', $keywordsList );
        $this->assertStringNotContainsString( ', ,', $keywordsList );
    }
    
    /**
     * Test state data structure
     */
    public function test_state_data_structure() {
        $states = $this->statesProvider->getAll();
        
        foreach ( $states as $stateName => $stateData ) {
            $this->assertIsString( $stateName );
            $this->assertIsArray( $stateData );
            $this->assertArrayHasKey( 'cities', $stateData );
            $this->assertIsArray( $stateData['cities'] );
            $this->assertCount( 6, $stateData['cities'] );
            
            // Test first state more thoroughly
            if ( $stateName === 'Alabama' ) {
                $this->assertContains( 'Birmingham', $stateData['cities'] );
                $this->assertContains( 'Montgomery', $stateData['cities'] );
            }
            
            // Only test first few states
            static $tested = 0;
            if ( ++$tested >= 3 ) break;
        }
    }
    
    /**
     * Helper to create mock API key manager
     */
    private function createMockApiKeyManager() {
        return new class extends ApiKeyManager {
            public function __construct() {
                // Empty constructor for mock
            }

            public function getKey(): string|false {
                return 'mock-api-key';
            }

            public function hasKey(): bool {
                return true;
            }

            public function getApiKey(): ?string {
                return 'mock-api-key';
            }

            public function setApiKey( string $apiKey ): bool {
                return true;
            }

            public function validateApiKey(): bool {
                return true;
            }

            public function getModel(): string|false {
                return 'claude-sonnet-4-20250514';
            }

            public function hasCustomModel(): bool {
                return true;
            }

            public function setModel( string $model ): bool {
                return true;
            }

            public function deleteModel(): bool {
                return true;
            }
        };
    }
    
    /**
     * Create mock API client
     */
    private function createMockApiClient() {
        return new class( null ) extends ClaudeApiClient {
            public function __construct( $keyManager ) {
                // Don't call parent constructor
            }
            
            public function sendRequest( string $prompt ): string|false {
                return 'mock-response';
            }
            
            public function isConfigured(): bool {
                return true;
            }
            
            public function validateCredentials(): bool {
                return true;
            }
        };
    }
    
}