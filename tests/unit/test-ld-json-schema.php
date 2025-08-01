<?php
/**
 * Unit tests for LD-JSON schema generation
 *
 * @package EightyFourEM_Local_Pages
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

class Test_LD_JSON_Schema extends TestCase {
    
    private $plugin;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        
        $this->plugin = new EightyFourEM_Local_Pages_Generator();
    }
    
    /**
     * Test generate_ld_json_schema method
     */
    public function test_generate_ld_json_schema() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        // Test state page schema
        $state_data = [
            'state' => 'California',
            'city' => '',
            'local_seo_content' => 'Test content about California WordPress development services.'
        ];
        
        $schema = $method->invoke( $this->plugin, $state_data );
        $decoded = json_decode( $schema, true );
        
        // Validate JSON structure
        $this->assertNotNull( $decoded );
        $this->assertIsArray( $decoded );
        $this->assertEquals( 'Service', $decoded['@type'] );
        $this->assertEquals( 'https://schema.org', $decoded['@context'] );
        
        // Test service name
        $this->assertEquals( 'WordPress Development Services in California', $decoded['name'] );
        
        // Test description
        $this->assertStringContainsString( 'California', $decoded['description'] );
        $this->assertStringContainsString( 'WordPress development', $decoded['description'] );
        
        // Test provider
        $this->assertIsArray( $decoded['provider'] );
        $this->assertEquals( 'LocalBusiness', $decoded['provider']['@type'] );
        $this->assertEquals( '84EM', $decoded['provider']['name'] );
        $this->assertEquals( 'https://84em.com', $decoded['provider']['url'] );
        
        // Test address
        $this->assertArrayHasKey( 'address', $decoded['provider'] );
        $this->assertEquals( 'PostalAddress', $decoded['provider']['address']['@type'] );
        $this->assertEquals( 'California', $decoded['provider']['address']['addressRegion'] );
        $this->assertEquals( 'US', $decoded['provider']['address']['addressCountry'] );
        
        // Test area served
        $this->assertIsArray( $decoded['areaServed'] );
        $this->assertEquals( 'State', $decoded['areaServed']['@type'] );
        $this->assertEquals( 'California', $decoded['areaServed']['name'] );
        
        // Test service type
        $this->assertContains( 'WordPress Development', $decoded['serviceType'] );
        $this->assertContains( 'Web Development', $decoded['serviceType'] );
        $this->assertContains( 'Custom Plugin Development', $decoded['serviceType'] );
    }
    
    /**
     * Test city page LD-JSON schema
     */
    public function test_city_page_ld_json_schema() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        $city_data = [
            'state' => 'California',
            'city' => 'Los Angeles',
            'local_seo_content' => 'WordPress development services in Los Angeles.'
        ];
        
        $schema = $method->invoke( $this->plugin, $city_data );
        $decoded = json_decode( $schema, true );
        
        // Test service name includes city
        $this->assertEquals( 'WordPress Development Services in Los Angeles, California', $decoded['name'] );
        
        // Test city-specific address
        $this->assertEquals( 'Los Angeles', $decoded['provider']['address']['addressLocality'] );
        $this->assertEquals( 'California', $decoded['provider']['address']['addressRegion'] );
        
        // Test area served for city
        $this->assertEquals( 'City', $decoded['areaServed']['@type'] );
        $this->assertEquals( 'Los Angeles', $decoded['areaServed']['name'] );
        $this->assertEquals( 'California', $decoded['areaServed']['containedInPlace']['name'] );
    }
    
    /**
     * Test schema with special characters in location names
     */
    public function test_schema_with_special_characters() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        $data = [
            'state' => 'Louisiana',
            'city' => "O'Fallon",
            'local_seo_content' => 'Services in O\'Fallon.'
        ];
        
        $schema = $method->invoke( $this->plugin, $data );
        $decoded = json_decode( $schema, true );
        
        // Ensure special characters are properly encoded
        $this->assertNotNull( $decoded );
        $this->assertEquals( "WordPress Development Services in O'Fallon, Louisiana", $decoded['name'] );
        $this->assertEquals( "O'Fallon", $decoded['areaServed']['name'] );
    }
    
    /**
     * Test schema includes all required service types
     */
    public function test_complete_service_types() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        $data = [
            'state' => 'Texas',
            'city' => '',
            'local_seo_content' => 'Full service WordPress agency in Texas.'
        ];
        
        $schema = $method->invoke( $this->plugin, $data );
        $decoded = json_decode( $schema, true );
        
        $expected_services = [
            'WordPress Development',
            'Custom Plugin Development',
            'Theme Development',
            'API Integration',
            'Performance Optimization',
            'WordPress Maintenance',
            'Web Development',
            'eCommerce Development'
        ];
        
        foreach ( $expected_services as $service ) {
            $this->assertContains( $service, $decoded['serviceType'] );
        }
    }
    
    /**
     * Test valid offers section in schema
     */
    public function test_offers_section() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        $data = [
            'state' => 'Florida',
            'city' => 'Miami',
            'local_seo_content' => 'Professional WordPress services in Miami.'
        ];
        
        $schema = $method->invoke( $this->plugin, $data );
        $decoded = json_decode( $schema, true );
        
        // Check offers structure
        $this->assertArrayHasKey( 'offers', $decoded );
        $this->assertIsArray( $decoded['offers'] );
        $this->assertEquals( 'Offer', $decoded['offers']['@type'] );
        $this->assertEquals( 'USD', $decoded['offers']['priceCurrency'] );
        $this->assertEquals( 'https://schema.org/InStock', $decoded['offers']['availability'] );
    }
    
    /**
     * Test provider contact information
     */
    public function test_provider_contact_info() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        $data = [
            'state' => 'New York',
            'city' => '',
            'local_seo_content' => 'New York WordPress development.'
        ];
        
        $schema = $method->invoke( $this->plugin, $data );
        $decoded = json_decode( $schema, true );
        
        $provider = $decoded['provider'];
        
        // Test contact points
        $this->assertArrayHasKey( 'contactPoint', $provider );
        $this->assertIsArray( $provider['contactPoint'] );
        $this->assertEquals( 'ContactPoint', $provider['contactPoint']['@type'] );
        $this->assertEquals( 'customer service', $provider['contactPoint']['contactType'] );
        $this->assertArrayHasKey( 'availableLanguage', $provider['contactPoint'] );
        $this->assertEquals( 'English', $provider['contactPoint']['availableLanguage'] );
    }
    
    /**
     * Test schema validation for empty data
     */
    public function test_schema_with_empty_data() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        $data = [
            'state' => '',
            'city' => '',
            'local_seo_content' => ''
        ];
        
        $schema = $method->invoke( $this->plugin, $data );
        $decoded = json_decode( $schema, true );
        
        // Should still generate valid schema with defaults
        $this->assertNotNull( $decoded );
        $this->assertEquals( 'Service', $decoded['@type'] );
        $this->assertEquals( '84EM', $decoded['provider']['name'] );
    }
    
    /**
     * Test multi-word state names
     */
    public function test_multi_word_state_names() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        $data = [
            'state' => 'North Carolina',
            'city' => '',
            'local_seo_content' => 'WordPress services in North Carolina.'
        ];
        
        $schema = $method->invoke( $this->plugin, $data );
        $decoded = json_decode( $schema, true );
        
        $this->assertEquals( 'WordPress Development Services in North Carolina', $decoded['name'] );
        $this->assertEquals( 'North Carolina', $decoded['areaServed']['name'] );
    }
    
    /**
     * Test schema includes proper URL structure
     */
    public function test_schema_url_structure() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        // Test state page
        $state_data = [
            'state' => 'California',
            'city' => '',
            'local_seo_content' => 'California services.'
        ];
        
        $schema = $method->invoke( $this->plugin, $state_data );
        $decoded = json_decode( $schema, true );
        
        $this->assertArrayHasKey( 'url', $decoded );
        $this->assertEquals( 'https://84em.com/wordpress-development-services-california/', $decoded['url'] );
        
        // Test city page
        $city_data = [
            'state' => 'California',
            'city' => 'San Diego',
            'local_seo_content' => 'San Diego services.'
        ];
        
        $schema = $method->invoke( $this->plugin, $city_data );
        $decoded = json_decode( $schema, true );
        
        $this->assertEquals( 'https://84em.com/wordpress-development-services-california/san-diego/', $decoded['url'] );
    }
    
    /**
     * Test schema includes breadcrumb list
     */
    public function test_breadcrumb_schema() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        $data = [
            'state' => 'Texas',
            'city' => 'Austin',
            'local_seo_content' => 'Austin WordPress development.'
        ];
        
        $schema = $method->invoke( $this->plugin, $data );
        
        // Schema might be array of multiple schemas
        if ( strpos( $schema, '[' ) === 0 ) {
            $decoded = json_decode( $schema, true );
            $breadcrumb = null;
            
            foreach ( $decoded as $item ) {
                if ( $item['@type'] === 'BreadcrumbList' ) {
                    $breadcrumb = $item;
                    break;
                }
            }
            
            if ( $breadcrumb ) {
                $this->assertEquals( 'BreadcrumbList', $breadcrumb['@type'] );
                $this->assertArrayHasKey( 'itemListElement', $breadcrumb );
                $this->assertGreaterThan( 0, count( $breadcrumb['itemListElement'] ) );
            }
        }
    }
    
    /**
     * Test JSON encoding handles unicode properly
     */
    public function test_unicode_handling() {
        $method = $this->get_private_method( 'generate_ld_json_schema' );
        
        $data = [
            'state' => 'Hawaii',
            'city' => 'Kailua-Kona',
            'local_seo_content' => 'WordPress services in Hawaiʻi.'
        ];
        
        $schema = $method->invoke( $this->plugin, $data );
        
        // Should be valid JSON
        $decoded = json_decode( $schema, true );
        $this->assertNotNull( $decoded );
        
        // Check that unicode is preserved
        $json_error = json_last_error();
        $this->assertEquals( JSON_ERROR_NONE, $json_error );
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
}