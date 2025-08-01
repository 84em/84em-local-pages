<?php
/**
 * Unit tests for URL generation and permalink handling
 *
 * @package EightyFourEM_Local_Pages
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

class Test_URL_Generation extends TestCase {
    
    private $plugin;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        
        $this->plugin = new EightyFourEM_Local_Pages_Generator();
    }
    
    /**
     * Test remove_slug_from_permalink method
     */
    public function test_remove_slug_from_permalink() {
        $method = $this->get_private_method( 'remove_slug_from_permalink' );
        
        // Create mock post object
        $post = new stdClass();
        $post->post_type = 'local';
        
        // Test state page URL
        $original_url = 'https://84em.com/local/wordpress-development-services-california/';
        $expected_url = 'https://84em.com/wordpress-development-services-california/';
        $result = $method->invoke( $this->plugin, $original_url, $post );
        $this->assertEquals( $expected_url, $result );
        
        // Test city page URL
        $original_url = 'https://84em.com/local/wordpress-development-services-california/los-angeles/';
        $expected_url = 'https://84em.com/wordpress-development-services-california/los-angeles/';
        $result = $method->invoke( $this->plugin, $original_url, $post );
        $this->assertEquals( $expected_url, $result );
        
        // Test non-local post type (should not change)
        $post->post_type = 'post';
        $original_url = 'https://84em.com/local/some-post/';
        $result = $method->invoke( $this->plugin, $original_url, $post );
        $this->assertEquals( $original_url, $result );
    }
    
    /**
     * Test state page URL generation
     */
    public function test_state_page_url_generation() {
        // Test various state names
        $test_cases = [
            'California' => 'wordpress-development-services-california',
            'New York' => 'wordpress-development-services-new-york',
            'North Carolina' => 'wordpress-development-services-north-carolina',
            'Rhode Island' => 'wordpress-development-services-rhode-island',
            'Washington' => 'wordpress-development-services-washington',
            'West Virginia' => 'wordpress-development-services-west-virginia',
        ];
        
        foreach ( $test_cases as $state => $expected_slug ) {
            $slug = 'wordpress-development-services-' . sanitize_title( $state );
            $this->assertEquals( $expected_slug, $slug );
            
            // Test full URL
            $url = home_url( '/' . $slug . '/' );
            $this->assertEquals( "https://84em.com/$expected_slug/", $url );
        }
    }
    
    /**
     * Test city page URL generation
     */
    public function test_city_page_url_generation() {
        // Test various city/state combinations
        $test_cases = [
            ['California', 'Los Angeles'] => 'wordpress-development-services-california/los-angeles',
            ['California', 'San Francisco'] => 'wordpress-development-services-california/san-francisco',
            ['New York', 'New York'] => 'wordpress-development-services-new-york/new-york',
            ['Louisiana', 'New Orleans'] => 'wordpress-development-services-louisiana/new-orleans',
            ['Michigan', 'Grand Rapids'] => 'wordpress-development-services-michigan/grand-rapids',
            ['Iowa', 'Cedar Rapids'] => 'wordpress-development-services-iowa/cedar-rapids',
        ];
        
        foreach ( $test_cases as $location => $expected_path ) {
            list( $state, $city ) = $location;
            $path = 'wordpress-development-services-' . sanitize_title( $state ) . '/' . sanitize_title( $city );
            $this->assertEquals( $expected_path, $path );
            
            // Test full URL
            $url = home_url( '/' . $path . '/' );
            $this->assertEquals( "https://84em.com/$expected_path/", $url );
        }
    }
    
    /**
     * Test special character handling in URLs
     */
    public function test_special_character_handling() {
        // Cities with special characters
        $test_cases = [
            "St. Paul" => 'st-paul',
            "O'Fallon" => 'ofallon',
            "Coeur d'Alene" => 'coeur-dalene',
            "Winston-Salem" => 'winston-salem',
            "Fort Worth" => 'fort-worth',
        ];
        
        foreach ( $test_cases as $city => $expected_slug ) {
            $slug = sanitize_title( $city );
            $this->assertEquals( $expected_slug, $slug );
        }
    }
    
    /**
     * Test service keyword URL mapping
     */
    public function test_service_keyword_url_mapping() {
        $property = $this->get_private_property( 'service_keywords' );
        $keywords = $property->getValue( $this->plugin );
        
        // Test that development-related keywords link to /work/
        $work_keywords = [
            'WordPress development',
            'custom plugin development',
            'API integrations',
            'web development'
        ];
        
        foreach ( $work_keywords as $keyword ) {
            if ( isset( $keywords[$keyword] ) ) {
                $this->assertStringContainsString( '/work/', $keywords[$keyword] );
            }
        }
        
        // Test that service keywords link to /services/
        $service_keywords = [
            'WordPress maintenance',
            'WordPress support',
            'data migration',
            'platform transfers'
        ];
        
        foreach ( $service_keywords as $keyword ) {
            if ( isset( $keywords[$keyword] ) ) {
                $this->assertStringContainsString( '/services/', $keywords[$keyword] );
            }
        }
        
        // Test location-specific keywords
        $this->assertStringContainsString( 'iowa/cedar-rapids', $keywords['Custom WordPress development Cedar Rapids'] );
        $this->assertStringContainsString( 'iowa/', $keywords['WordPress development agency Iowa'] );
    }
    
    /**
     * Test add_rewrite_rules functionality
     */
    public function test_rewrite_rules_patterns() {
        // Test state page pattern
        $state_pattern = 'wordpress-development-services-([^/]+)/?$';
        $this->assertMatchesRegularExpression( 
            '/^' . str_replace( '/', '\/', $state_pattern ) . '$/', 
            'wordpress-development-services-california/'
        );
        
        // Test city page pattern
        $city_pattern = 'wordpress-development-services-([^/]+)/([^/]+)/?$';
        $this->assertMatchesRegularExpression(
            '/^' . str_replace( '/', '\/', $city_pattern ) . '$/',
            'wordpress-development-services-california/los-angeles/'
        );
    }
    
    /**
     * Test URL consistency across the plugin
     */
    public function test_url_consistency() {
        $property = $this->get_private_property( 'us_states_data' );
        $states = $property->getValue( $this->plugin );
        
        // Ensure all state URLs follow the pattern
        foreach ( array_keys( $states ) as $state ) {
            $url = home_url( '/wordpress-development-services-' . sanitize_title( $state ) . '/' );
            $this->assertStringStartsWith( 'https://84em.com/', $url );
            $this->assertStringEndsWith( '/', $url );
            $this->assertStringContainsString( 'wordpress-development-services-', $url );
        }
    }
    
    /**
     * Test sitemap URL generation
     */
    public function test_sitemap_url() {
        $sitemap_url = home_url( '/sitemap-local.xml' );
        $this->assertEquals( 'https://84em.com/sitemap-local.xml', $sitemap_url );
    }
    
    /**
     * Test index page URL
     */
    public function test_index_page_url() {
        $index_url = home_url( '/wordpress-development-services-usa/' );
        $this->assertEquals( 'https://84em.com/wordpress-development-services-usa/', $index_url );
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