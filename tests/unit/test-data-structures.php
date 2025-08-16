<?php
/**
 * Unit tests for data structures
 *
 * @package EightyFourEM_Local_Pages
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;

class Test_Data_Structures extends TestCase {

    private StatesProvider $statesProvider;
    private KeywordsProvider $keywordsProvider;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        $this->statesProvider   = new StatesProvider();
        $this->keywordsProvider = new KeywordsProvider();
    }

    /**
     * Test service keywords structure
     */
    public function test_service_keywords_structure() {
        $keywords = $this->keywordsProvider->getAll();

        // Test it's an associative array
        $this->assertIsArray( $keywords );
        $this->assertNotEmpty( $keywords );

        // Test each keyword has a URL
        foreach ( $keywords as $keyword => $url ) {
            $this->assertIsString( $keyword );
            $this->assertIsString( $url );
            $this->assertNotEmpty( $keyword );
            $this->assertNotEmpty( $url );

            // URLs should be valid
            $this->assertMatchesRegularExpression( '/^https?:\/\//', $url );
        }

        // Test specific keywords exist
        $this->assertArrayHasKey( 'WordPress development', $keywords );
        $this->assertArrayHasKey( 'custom plugin development', $keywords );
        $this->assertArrayHasKey( 'API integrations', $keywords );
        $this->assertArrayHasKey( 'WordPress maintenance', $keywords );
    }

    /**
     * Test US states data structure
     */
    public function test_us_states_data_structure() {
        $states = $this->statesProvider->getAll();

        // Should have exactly 50 states
        $this->assertCount( 50, $states );

        // Test each state structure
        foreach ( $states as $state => $data ) {
            $this->assertIsString( $state );
            $this->assertIsArray( $data );
            $this->assertArrayHasKey( 'cities', $data );
            $this->assertIsArray( $data['cities'] );
            $this->assertCount( 6, $data['cities'] );

            // Each city should be a non-empty string
            foreach ( $data['cities'] as $city ) {
                $this->assertIsString( $city );
                $this->assertNotEmpty( $city );
            }
        }

        // Test specific states
        $this->assertArrayHasKey( 'California', $states );
        $this->assertArrayHasKey( 'Texas', $states );
        $this->assertArrayHasKey( 'New York', $states );
        $this->assertArrayHasKey( 'Wyoming', $states );

        // Test California cities
        $california_cities = $states['California']['cities'];
        $this->assertContains( 'Los Angeles', $california_cities );
        $this->assertContains( 'San Francisco', $california_cities );
        $this->assertContains( 'San Diego', $california_cities );
    }


    /**
     * Test service keywords list generation for prompts
     */
    public function test_service_keywords_list_generation() {
        $keywords = $this->keywordsProvider->getAll();

        // Generate list like in the actual method
        $service_keywords_list = implode( ', ', array_keys( $keywords ) );

        // Should be a comma-separated string
        $this->assertIsString( $service_keywords_list );
        $this->assertStringContainsString( ', ', $service_keywords_list );
        $this->assertStringContainsString( 'WordPress development', $service_keywords_list );
        $this->assertStringContainsString( 'custom plugin development', $service_keywords_list );
    }

    /**
     * Test initialization of properties
     */
    public function test_properties_initialization() {
        // Test that providers are properly initialized and return data
        $this->assertInstanceOf( StatesProvider::class, $this->statesProvider );
        $this->assertInstanceOf( KeywordsProvider::class, $this->keywordsProvider );

        // Verify they provide data
        $this->assertNotEmpty( $this->statesProvider->getAll() );
        $this->assertNotEmpty( $this->keywordsProvider->getAll() );
    }

    /**
     * Test that all states have valid city data
     */
    public function test_all_states_have_valid_cities() {
        $states = $this->statesProvider->getAll();

        $expected_states = [
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California',
            'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia',
            'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
            'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland',
            'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri',
            'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey',
            'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
            'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina',
            'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
            'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
        ];

        foreach ( $expected_states as $state ) {
            $this->assertArrayHasKey( $state, $states, "Missing state: $state" );
            $this->assertCount( 6, $states[$state]['cities'], "State $state doesn't have 6 cities" );
        }
    }
}
