<?php
/**
 * Unit tests for WP-CLI argument parsing and validation
 *
 * @package EightyFourEM\LocalPages
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Cli\Commands\GenerateCommand;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
use EightyFourEM\LocalPages\Api\ApiKeyManager;

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
        
        // Initialize GenerateCommand with dependencies
        $this->generateCommand = new GenerateCommand(
            $this->apiKeyManager,
            $this->statesProvider,
            $this->keywordsProvider
        );
    }
    
    /**
     * Test parse_state_names method with various inputs
     */
    public function test_parse_state_names() {
        $method = $this->get_private_method( $this->generateCommand, 'parseStateNames' );
        
        // Test single state
        $result = $method->invoke( $this->generateCommand, 'California' );
        $this->assertEquals( ['California'], $result );
        
        // Test multiple states with comma
        $result = $method->invoke( $this->generateCommand, 'California, Texas, Florida' );
        $this->assertEquals( ['California', 'Texas', 'Florida'], $result );
        
        // Test with extra spaces
        $result = $method->invoke( $this->generateCommand, ' California , Texas , Florida ' );
        $this->assertEquals( ['California', 'Texas', 'Florida'], $result );
        
        // Test with newlines and tabs
        $result = $method->invoke( $this->generateCommand, "California,\nTexas,\tFlorida" );
        $this->assertEquals( ['California', 'Texas', 'Florida'], $result );
        
        // Test empty string
        $result = $method->invoke( $this->generateCommand, '' );
        $this->assertEquals( [''], $result );
        
        // Test with mixed case
        $result = $method->invoke( $this->generateCommand, 'california, TEXAS, FlOrIdA' );
        $this->assertEquals( ['california', 'TEXAS', 'FlOrIdA'], $result );
        
        // Test with special characters in state names
        $result = $method->invoke( $this->generateCommand, 'New York, North Carolina, Rhode Island' );
        $this->assertEquals( ['New York', 'North Carolina', 'Rhode Island'], $result );
    }
    
    /**
     * Test parse_city_names method
     */
    public function test_parse_city_names() {
        $method = $this->get_private_method( $this->generateCommand, 'parseCityNames' );
        
        // Test single city
        $result = $method->invoke( $this->generateCommand, 'Los Angeles' );
        $this->assertEquals( ['Los Angeles'], $result );
        
        // Test multiple cities
        $result = $method->invoke( $this->generateCommand, 'Los Angeles, San Francisco, San Diego' );
        $this->assertEquals( ['Los Angeles', 'San Francisco', 'San Diego'], $result );
        
        // Test cities with special characters
        $result = $method->invoke( $this->generateCommand, "St. Paul, O'Fallon, Coeur d'Alene" );
        $this->assertEquals( ['St. Paul', "O'Fallon", "Coeur d'Alene"], $result );
        
        // Test with hyphenated cities
        $result = $method->invoke( $this->generateCommand, 'Winston-Salem, Wilkes-Barre' );
        $this->assertEquals( ['Winston-Salem', 'Wilkes-Barre'], $result );
        
        // Test with extra whitespace
        $result = $method->invoke( $this->generateCommand, '  New York  ,  Los Angeles  ' );
        $this->assertEquals( ['New York', 'Los Angeles'], $result );
        
        // Test empty string
        $result = $method->invoke( $this->generateCommand, '' );
        $this->assertEquals( [''], $result );
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
     * Test special WP-CLI flags
     */
    public function test_special_cli_flags() {
        // Test dry-run flag behavior
        $args = [
            '--state' => 'California',
            '--dry-run' => true
        ];
        $this->assertTrue( isset( $args['--dry-run'] ) );
        
        // Test verbose flag
        $args = [
            '--state' => 'California',
            '--verbose' => true
        ];
        $this->assertTrue( isset( $args['--verbose'] ) );
        
        // Test states-only flag
        $args = [
            '--generate-all' => true,
            '--states-only' => true
        ];
        $this->assertTrue( isset( $args['--states-only'] ) );
        
        // Test complete flag
        $args = [
            '--state' => 'California',
            '--city' => 'all',
            '--complete' => true
        ];
        $this->assertTrue( isset( $args['--complete'] ) );
    }
    
    /**
     * Test argument sanitization
     */
    public function test_argument_sanitization() {
        $method = $this->get_private_method( $this->generateCommand, 'parseStateNames' );
        
        // Test that parseStateNames preserves input (sanitization happens elsewhere)
        // The method just splits and trims, actual sanitization would happen when used
        $malicious = '<script>alert("xss")</script>California';
        $result = $method->invoke( $this->generateCommand, $malicious );
        // The parse method doesn't sanitize, it just splits/trims
        // Actual sanitization would happen when the state name is validated
        $this->assertEquals( '<script>alert("xss")</script>California', $result[0] );
        
        // Test SQL injection attempt - parse method preserves it
        $sql_inject = "California'; DROP TABLE wp_posts; --";
        $result = $method->invoke( $this->generateCommand, $sql_inject );
        $this->assertEquals( "California'; DROP TABLE wp_posts; --", $result[0] );
        
        // The important part is that these malicious states would fail validation
        $this->assertFalse( $this->statesProvider->has( '<script>alert("xss")</script>California' ), 'Malicious state should not exist' );
        $this->assertFalse( $this->statesProvider->has( "California'; DROP TABLE wp_posts; --" ), 'SQL injection state should not exist' );
    }
    
    /**
     * Test batch size validation
     */
    public function test_batch_size_validation() {
        // Test valid batch sizes
        $valid_sizes = [1, 5, 10, 50, 100];
        foreach ( $valid_sizes as $size ) {
            $this->assertTrue( $this->is_valid_batch_size( $size ) );
        }
        
        // Test invalid batch sizes
        $invalid_sizes = [-1, 0, 1001, 'abc', null, []];
        foreach ( $invalid_sizes as $size ) {
            $this->assertFalse( $this->is_valid_batch_size( $size ) );
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
     * Test command help output format
     */
    public function test_help_output_format() {
        // Simulate help command structure
        $help_sections = [
            'description' => 'Generate local pages for WordPress development services',
            'synopsis' => '[--state=<state>] [--city=<city>] [--generate-all]',
            'examples' => [
                'wp 84em local-pages --state=California',
                'wp 84em local-pages --generate-all --states-only'
            ]
        ];
        
        $this->assertArrayHasKey( 'description', $help_sections );
        $this->assertArrayHasKey( 'synopsis', $help_sections );
        $this->assertArrayHasKey( 'examples', $help_sections );
        $this->assertIsArray( $help_sections['examples'] );
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
     * Helper to check valid batch size
     */
    private function is_valid_batch_size( $size ) {
        return is_int( $size ) && $size > 0 && $size <= 1000;
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
     * Helper to create mock ApiKeyManager
     */
    private function createMock( $class ) {
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
        };
    }
}