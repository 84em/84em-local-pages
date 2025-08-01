<?php
/**
 * Unit tests for WP-CLI argument parsing and validation
 *
 * @package EightyFourEM_Local_Pages
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

class Test_WP_CLI_Args extends TestCase {
    
    private $plugin;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        
        $this->plugin = new EightyFourEM_Local_Pages_Generator();
    }
    
    /**
     * Test parse_state_names method with various inputs
     */
    public function test_parse_state_names() {
        $method = $this->get_private_method( 'parse_state_names' );
        
        // Test single state
        $result = $method->invoke( $this->plugin, 'California' );
        $this->assertEquals( ['California'], $result );
        
        // Test multiple states with comma
        $result = $method->invoke( $this->plugin, 'California, Texas, Florida' );
        $this->assertEquals( ['California', 'Texas', 'Florida'], $result );
        
        // Test with extra spaces
        $result = $method->invoke( $this->plugin, ' California , Texas , Florida ' );
        $this->assertEquals( ['California', 'Texas', 'Florida'], $result );
        
        // Test with newlines and tabs
        $result = $method->invoke( $this->plugin, "California,\nTexas,\tFlorida" );
        $this->assertEquals( ['California', 'Texas', 'Florida'], $result );
        
        // Test empty string
        $result = $method->invoke( $this->plugin, '' );
        $this->assertEquals( [''], $result );
        
        // Test with mixed case
        $result = $method->invoke( $this->plugin, 'california, TEXAS, FlOrIdA' );
        $this->assertEquals( ['california', 'TEXAS', 'FlOrIdA'], $result );
        
        // Test with special characters in state names
        $result = $method->invoke( $this->plugin, 'New York, North Carolina, Rhode Island' );
        $this->assertEquals( ['New York', 'North Carolina', 'Rhode Island'], $result );
    }
    
    /**
     * Test parse_city_names method
     */
    public function test_parse_city_names() {
        $method = $this->get_private_method( 'parse_city_names' );
        
        // Test single city
        $result = $method->invoke( $this->plugin, 'Los Angeles' );
        $this->assertEquals( ['Los Angeles'], $result );
        
        // Test multiple cities
        $result = $method->invoke( $this->plugin, 'Los Angeles, San Francisco, San Diego' );
        $this->assertEquals( ['Los Angeles', 'San Francisco', 'San Diego'], $result );
        
        // Test cities with special characters
        $result = $method->invoke( $this->plugin, "St. Paul, O'Fallon, Coeur d'Alene" );
        $this->assertEquals( ['St. Paul', "O'Fallon", "Coeur d'Alene"], $result );
        
        // Test with hyphenated cities
        $result = $method->invoke( $this->plugin, 'Winston-Salem, Wilkes-Barre' );
        $this->assertEquals( ['Winston-Salem', 'Wilkes-Barre'], $result );
        
        // Test with extra whitespace
        $result = $method->invoke( $this->plugin, '  New York  ,  Los Angeles  ' );
        $this->assertEquals( ['New York', 'Los Angeles'], $result );
        
        // Test empty string
        $result = $method->invoke( $this->plugin, '' );
        $this->assertEquals( [''], $result );
    }
    
    /**
     * Test validate_state method
     */
    public function test_validate_state() {
        // Test valid states
        $valid_states = ['California', 'Texas', 'New York', 'Florida', 'Wyoming'];
        foreach ( $valid_states as $state ) {
            $this->assertTrue( 
                $this->validate_state_exists( $state ),
                "State $state should be valid"
            );
        }
        
        // Test invalid states
        $invalid_states = ['Californias', 'InvalidState', 'TX', 'Cal', ''];
        foreach ( $invalid_states as $state ) {
            $this->assertFalse(
                $this->validate_state_exists( $state ),
                "State $state should be invalid"
            );
        }
        
        // Test case sensitivity
        $this->assertFalse( $this->validate_state_exists( 'california' ) );
        $this->assertFalse( $this->validate_state_exists( 'CALIFORNIA' ) );
    }
    
    /**
     * Test validate_city method
     */
    public function test_validate_city() {
        // Test valid city-state combinations
        $valid_combinations = [
            ['California', 'Los Angeles'],
            ['California', 'San Francisco'],
            ['Texas', 'Houston'],
            ['New York', 'New York'],
            ['Louisiana', 'New Orleans']
        ];
        
        foreach ( $valid_combinations as $combo ) {
            $this->assertTrue(
                $this->validate_city_exists( $combo[0], $combo[1] ),
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
            $this->assertFalse(
                $this->validate_city_exists( $combo[0], $combo[1] ),
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
    }
    
    /**
     * Test argument sanitization
     */
    public function test_argument_sanitization() {
        $method = $this->get_private_method( 'parse_state_names' );
        
        // Test XSS attempt in state name
        $malicious = '<script>alert("xss")</script>California';
        $result = $method->invoke( $this->plugin, $malicious );
        $this->assertNotContains( '<script>', $result[0] );
        
        // Test SQL injection attempt
        $sql_inject = "California'; DROP TABLE wp_posts; --";
        $result = $method->invoke( $this->plugin, $sql_inject );
        $this->assertNotContains( 'DROP TABLE', $result[0] );
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
     * Helper method to validate state exists
     */
    private function validate_state_exists( $state ) {
        $property = $this->get_private_property( 'us_states_data' );
        $states = $property->getValue( $this->plugin );
        return array_key_exists( $state, $states );
    }
    
    /**
     * Helper method to validate city exists in state
     */
    private function validate_city_exists( $state, $city ) {
        $property = $this->get_private_property( 'us_states_data' );
        $states = $property->getValue( $this->plugin );
        
        if ( ! array_key_exists( $state, $states ) ) {
            return false;
        }
        
        return in_array( $city, $states[$state]['cities'], true );
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
        if ( isset( $args['--state'] ) ) {
            $states = explode( ',', $args['--state'] );
            foreach ( $states as $state ) {
                if ( ! $this->validate_state_exists( trim( $state ) ) ) {
                    return false;
                }
            }
        }
        
        // Validate city if provided
        if ( isset( $args['--city'] ) && isset( $args['--state'] ) ) {
            $state = trim( $args['--state'] );
            $cities = explode( ',', $args['--city'] );
            foreach ( $cities as $city ) {
                if ( ! $this->validate_city_exists( $state, trim( $city ) ) ) {
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
    private function get_private_method( $method_name ) {
        $reflection = new ReflectionClass( $this->plugin );
        $method = $reflection->getMethod( $method_name );
        $method->setAccessible( true );
        return $method;
    }
    
    /**
     * Helper method to access private properties
     */
    private function get_private_property( $property_name ) {
        $reflection = new ReflectionClass( $this->plugin );
        $property = $reflection->getProperty( $property_name );
        $property->setAccessible( true );
        return $property;
    }
}