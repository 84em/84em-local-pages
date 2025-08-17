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




}
