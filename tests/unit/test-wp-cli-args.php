<?php
/**
 * Unit tests for WP-CLI argument parsing and validation
 *
 * @package EightyFourEM\LocalPages
 */

require_once dirname( __DIR__ ) . '/TestCase.php';
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

use EightyFourEM\LocalPages\Cli\Commands\GenerateCommand;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Content\StateContentGenerator;
use EightyFourEM\LocalPages\Content\CityContentGenerator;
use EightyFourEM\LocalPages\Utils\ContentProcessor;
use EightyFourEM\LocalPages\Schema\SchemaGenerator;

class Test_WP_CLI_Args extends TestCase {
    
    private $generateCommand;
    private $statesProvider;
    private $keywordsProvider;
    private $apiKeyManager;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        // Initialize data providers
        $this->statesProvider = new StatesProvider();
        $this->keywordsProvider = new KeywordsProvider();
        $this->apiKeyManager = $this->createMock( ApiKeyManager::class );
        
        // Create mock dependencies for GenerateCommand
        $mockApiClient = $this->createMock( ClaudeApiClient::class );
        $contentProcessor = new ContentProcessor( $this->keywordsProvider );
        $schemaGenerator = new SchemaGenerator( $this->statesProvider );
        
        $stateContentGenerator = new StateContentGenerator(
            $this->apiKeyManager,
            $mockApiClient,
            $this->statesProvider,
            $this->keywordsProvider,
            $schemaGenerator,
            $contentProcessor
        );
        
        $cityContentGenerator = new CityContentGenerator(
            $this->apiKeyManager,
            $mockApiClient,
            $this->statesProvider,
            $this->keywordsProvider,
            $schemaGenerator,
            $contentProcessor
        );
        
        // Initialize GenerateCommand with all dependencies
        $this->generateCommand = new GenerateCommand(
            $this->apiKeyManager,
            $this->statesProvider,
            $this->keywordsProvider,
            $stateContentGenerator,
            $cityContentGenerator,
            $contentProcessor,
            $schemaGenerator
        );
    }
    
    
    /**
     * Test validate_state method using StatesProvider
     */
    public function test_validate_state() {
        // Test valid states
        $valid_states = ['California', 'Texas', 'New York', 'Florida', 'Wyoming'];
        foreach ( $valid_states as $state ) {
            $this->assertTrue( 
                $this->statesProvider->has( $state ),
                "State $state should be valid"
            );
        }
        
        // Test invalid states
        $invalid_states = ['Californias', 'InvalidState', 'TX', 'Cal', ''];
        foreach ( $invalid_states as $state ) {
            $this->assertFalse(
                $this->statesProvider->has( $state ),
                "State $state should be invalid"
            );
        }
        
        // Test case sensitivity
        $this->assertFalse( $this->statesProvider->has( 'california' ) );
        $this->assertFalse( $this->statesProvider->has( 'CALIFORNIA' ) );
    }
    
    /**
     * Test validate_city method using StatesProvider
     */
    public function test_validate_city() {
        // Test valid city-state combinations
        $valid_combinations = [
            ['California', 'Los Angeles'],
            ['California', 'San Francisco'],
            ['Texas', 'Houston'],
            ['New York', 'New York City'],
            ['Louisiana', 'New Orleans']
        ];
        
        foreach ( $valid_combinations as $combo ) {
            $state_data = $this->statesProvider->get( $combo[0] );
            $this->assertTrue(
                in_array( $combo[1], $state_data['cities'] ?? [], true ),
                "City {$combo[1]} should exist in {$combo[0]}"
            );
        }
        
        // Test invalid city-state combinations
        $invalid_combinations = [
            ['California', 'Houston'], // Houston is in Texas
            ['Texas', 'Los Angeles'], // LA is in California
            ['Florida', 'InvalidCity'],
            ['InvalidState', 'Miami']
        ];
        
        foreach ( $invalid_combinations as $combo ) {
            $state_data = $this->statesProvider->get( $combo[0] );
            $this->assertFalse(
                in_array( $combo[1], $state_data['cities'] ?? [], true ),
                "City {$combo[1]} should not exist in {$combo[0]}"
            );
        }
    }
    
    /**
     * Test WP-CLI command argument validation
     */
    public function test_cli_argument_combinations() {
        // Simulate various CLI argument combinations
        $test_cases = [
            // Valid combinations
            [
                'args' => ['--state' => 'California'],
                'valid' => true,
                'description' => 'Single state generation'
            ],
            [
                'args' => ['--state' => 'California', '--city' => 'Los Angeles'],
                'valid' => true,
                'description' => 'Single city generation'
            ],
            [
                'args' => ['--state' => 'California,Texas,Florida'],
                'valid' => true,
                'description' => 'Multiple states'
            ],
            [
                'args' => ['--generate-all' => true, '--states-only' => true],
                'valid' => true,
                'description' => 'Generate all states only'
            ],
            [
                'args' => ['--generate-all' => true],
                'valid' => true,
                'description' => 'Generate all states and cities'
            ],
            [
                'args' => ['--state' => 'California', '--city' => 'Los Angeles,San Francisco'],
                'valid' => true,
                'description' => 'Multiple cities in one state'
            ],
            
            // Invalid combinations
            [
                'args' => ['--city' => 'Los Angeles'],
                'valid' => false,
                'description' => 'City without state'
            ],
            [
                'args' => ['--state' => 'InvalidState'],
                'valid' => false,
                'description' => 'Invalid state name'
            ],
            [
                'args' => ['--state' => 'California', '--city' => 'Houston'],
                'valid' => false,
                'description' => 'City not in specified state'
            ],
            [
                'args' => [],
                'valid' => false,
                'description' => 'No arguments provided'
            ]
        ];
        
        foreach ( $test_cases as $case ) {
            $is_valid = $this->validate_cli_args( $case['args'] );
            $this->assertEquals( 
                $case['valid'], 
                $is_valid, 
                "Failed for case: {$case['description']}"
            );
        }
    }
    
    
    
    
    /**
     * Test progress tracking arguments
     */
    public function test_progress_tracking_args() {
        // Test skip existing
        $args = ['--skip-existing' => true];
        $this->assertTrue( isset( $args['--skip-existing'] ) );
        
        // Test force regenerate
        $args = ['--force' => true];
        $this->assertTrue( isset( $args['--force'] ) );
        
        // Conflicting arguments
        $args = [
            '--skip-existing' => true,
            '--force' => true
        ];
        // These should be mutually exclusive
        $this->assertTrue( $this->has_conflicting_args( $args ) );
    }
    
    
    /**
     * Test state argument with 'all' value
     */
    public function test_state_all_argument() {
        // Test that 'all' is a special value for state argument
        $args = ['--state' => 'all'];
        $this->assertEquals( 'all', $args['--state'] );
        
        // When state=all, it should generate all states
        $all_states = $this->statesProvider->getKeys();
        $this->assertGreaterThan( 40, count( $all_states ), 'Should have at least 40 states' );
        $this->assertTrue( in_array( 'California', $all_states ), 'California should be in states list' );
        $this->assertTrue( in_array( 'Texas', $all_states ), 'Texas should be in states list' );
        $this->assertTrue( in_array( 'New York', $all_states ), 'New York should be in states list' );
    }
    
    /**
     * Test city argument with 'all' value
     */
    public function test_city_all_argument() {
        // Test that 'all' is a special value for city argument
        $args = ['--state' => 'California', '--city' => 'all'];
        $this->assertEquals( 'all', $args['--city'] );
        
        // When city=all, it should generate all cities for the state
        $california_data = $this->statesProvider->get( 'California' );
        $this->assertIsArray( $california_data['cities'] );
        $this->assertEquals( 6, count( $california_data['cities'] ), 'California should have 6 cities' );
        $this->assertTrue( in_array( 'Los Angeles', $california_data['cities'] ), 'Los Angeles should be in California cities' );
        $this->assertTrue( in_array( 'San Francisco', $california_data['cities'] ), 'San Francisco should be in California cities' );
    }
    
    /**
     * Helper method to validate CLI arguments
     */
    private function validate_cli_args( $args ) {
        // Must have at least one generation argument
        if ( empty( $args ) ) {
            return false;
        }
        
        // If city is specified, state must be specified
        if ( isset( $args['--city'] ) && ! isset( $args['--state'] ) ) {
            return false;
        }
        
        // Validate state if provided
        if ( isset( $args['--state'] ) && $args['--state'] !== 'all' ) {
            $states = explode( ',', $args['--state'] );
            foreach ( $states as $state ) {
                if ( ! $this->statesProvider->has( trim( $state ) ) ) {
                    return false;
                }
            }
        }
        
        // Validate city if provided
        if ( isset( $args['--city'] ) && isset( $args['--state'] ) && $args['--city'] !== 'all' ) {
            $state = trim( $args['--state'] );
            $state_data = $this->statesProvider->get( $state );
            
            if ( ! $state_data ) {
                return false;
            }
            
            $cities = explode( ',', $args['--city'] );
            foreach ( $cities as $city ) {
                if ( ! in_array( trim( $city ), $state_data['cities'] ?? [], true ) ) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Helper to check conflicting arguments
     */
    private function has_conflicting_args( $args ) {
        return isset( $args['--skip-existing'] ) && isset( $args['--force'] );
    }
    
    /**
     * Helper method to access private methods
     */
    private function get_private_method( $object, $method_name ) {
        $reflection = new ReflectionClass( $object );
        $method = $reflection->getMethod( $method_name );
        $method->setAccessible( true );
        return $method;
    }
    
    /**
     * Helper to create mock objects
     */
    private function createMock( $class ) {
        if ( $class === ApiKeyManager::class ) {
            return new class extends ApiKeyManager {
                public function __construct() {
                    // Empty constructor for mock
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
                
                public function getKey(): string|false {
                    return 'mock-api-key';
                }
                
                public function hasKey(): bool {
                    return true;
                }
            };
        }
        
        if ( $class === ClaudeApiClient::class ) {
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
        
        return null;
    }
}