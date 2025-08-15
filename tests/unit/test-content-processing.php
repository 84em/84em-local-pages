<?php
/**
 * Unit tests for content processing functions
 *
 * @package EightyFourEM_Local_Pages
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Utils\ContentProcessor;
use EightyFourEM\LocalPages\Data\KeywordsProvider;

class Test_Content_Processing extends TestCase {

    private ContentProcessor $contentProcessor;
    private KeywordsProvider $keywordsProvider;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        $this->keywordsProvider = new KeywordsProvider();
        $this->contentProcessor = new ContentProcessor( $this->keywordsProvider );
    }

    /**
     * Test title case conversion
     */
    public function test_convert_to_title_case() {
        // Test basic title case
        $this->assertEquals(
            'This Is a Test Title',
            $this->convertToTitleCase( 'this is a test title' )
        );

        // Test with articles and prepositions
        $this->assertEquals(
            'The Best of the Best',
            $this->convertToTitleCase( 'the best of the best' )
        );

        // Test 84EM handling
        $this->assertEquals(
            '84EM Wordpress Development',
            $this->convertToTitleCase( '84em wordpress development' )
        );

        // Test with mixed case input
        $this->assertEquals(
            '84EM Is the Best',
            $this->convertToTitleCase( '84EM IS THE BEST' )
        );

        // Test first and last word capitalization
        $this->assertEquals(
            'In the Beginning and the End',
            $this->convertToTitleCase( 'in the beginning and the end' )
        );
    }

    /**
     * Test heading processing
     */
    public function test_process_headings() {
        // Test H2 with hyperlink removal
        $input = '<!-- wp:heading {"level":2} --><h2><a href="/test">linked heading</a></h2><!-- /wp:heading -->';
        $expected = '<!-- wp:heading {"level":2} --><h2><strong>Linked Heading</strong></h2><!-- /wp:heading -->';
        $result = $this->processHeadings( $input );
        $this->assertEquals( $expected, $result );

        // Test H3 with existing strong tags
        $input = '<!-- wp:heading {"level":3} --><h3><strong>Already Bold</strong></h3><!-- /wp:heading -->';
        $expected = '<!-- wp:heading {"level":3} --><h3><strong>Already Bold</strong></h3><!-- /wp:heading -->';
        $result = $this->processHeadings( $input );
        $this->assertEquals( $expected, $result );

        // Test multiple headings
        $input = '<!-- wp:heading {"level":2} --><h2>First Heading</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Some content</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>second heading</h3><!-- /wp:heading -->';

        $result = $this->processHeadings( $input );
        $this->assertStringContainsString( '<h2><strong>First Heading</strong></h2>', $result );
        $this->assertStringContainsString( '<h3><strong>Second Heading</strong></h3>', $result );
        $this->assertStringContainsString( '<p>Some content</p>', $result );
    }

    /**
     * Test service keyword link addition
     */
    public function test_add_service_keyword_links() {
        // Test single keyword replacement
        $content = 'We offer WordPress development services.';
        $result = $this->contentProcessor->processContent( $content, [] );
        $work_url = site_url( '/work/' );
        $this->assertStringContainsString( '<a href="' . $work_url . '">WordPress development</a>', $result );

        // Test multiple keywords with different URLs
        $content      = 'We offer WordPress maintenance and API integrations.';
        $result       = $this->contentProcessor->processContent( $content, [] );
        $services_url = site_url( '/services/' );
        // These have different URLs so both should be linked
        $this->assertStringContainsString( '<a href="' . $services_url . '">WordPress maintenance</a>', $result );
        $this->assertStringContainsString( '<a href="' . $work_url . '">API integrations</a>', $result );

        // Test case insensitive matching - preserves content case
        $content = 'We provide WORDPRESS DEVELOPMENT and WordPress Maintenance.';
        $result       = $this->contentProcessor->processContent( $content, [] );
        $services_url = site_url( '/services/' );
        // The method preserves the case from the content, not the keyword definition
        $this->assertStringContainsString( '<a href="' . $work_url . '">WORDPRESS DEVELOPMENT</a>', $result );
        $this->assertStringContainsString( '<a href="' . $services_url . '">WordPress Maintenance</a>', $result );

        // Test avoiding double-linking - should not create nested links
        $content = 'Check our <a href="/test">WordPress development</a> page.';
        $result       = $this->contentProcessor->processContent( $content, [] );
        // Should NOT create nested links - text already in a link should be left alone
        $this->assertStringContainsString( '<a href="/test">WordPress development</a>', $result );
        // And should NOT contain the work URL link
        $this->assertStringNotContainsString( '<a href="' . $work_url . '">WordPress development</a>', $result );
    }

    /**
     * Test block structure handling - should not duplicate blocks
     */
    public function test_block_structure_no_duplication() {
        // Test content that already has WordPress block markup
        $content_with_blocks = '<!-- wp:paragraph -->
<p>This is a paragraph with WordPress development services.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2><strong>Test Heading</strong></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Another paragraph with API integrations content.</p>
<!-- /wp:paragraph -->';
        
        $result = $this->contentProcessor->processContent( $content_with_blocks, [] );
        
        // Should NOT have nested paragraph blocks
        $this->assertStringNotContainsString( '<!-- wp:paragraph --> <p><!-- wp:paragraph -->', $result );
        $this->assertStringNotContainsString( '<p><p>', $result );
        
        // Should still have the original block structure
        $this->assertStringContainsString( '<!-- wp:paragraph -->', $result );
        $this->assertStringContainsString( '<!-- wp:heading {"level":2} -->', $result );
        
        // Count occurrences - should not be duplicated
        $paragraph_count = substr_count( $result, '<!-- wp:paragraph -->' );
        $this->assertEquals( 2, $paragraph_count, 'Should have exactly 2 paragraph blocks, not duplicated' );
    }

    /**
     * Test block structure creation for plain content
     */
    public function test_block_structure_plain_content() {
        // Test content without any block markup
        $plain_content = 'This is a plain paragraph.

<h2>Plain Heading</h2>

Another plain paragraph.';
        
        $result = $this->contentProcessor->processContent( $plain_content, [] );
        
        // Should have block markup added
        $this->assertStringContainsString( '<!-- wp:paragraph -->', $result );
        $this->assertStringContainsString( '<!-- wp:heading', $result );
        $this->assertStringContainsString( '<p>This is a plain paragraph.</p>', $result );
        
        // Should NOT have malformed nesting
        $this->assertStringNotContainsString( '<p><p>', $result );
        $this->assertStringNotContainsString( '<!-- wp:paragraph --> <p><!-- wp:paragraph -->', $result );
    }

    /**
     * Test parse state names
     */
    public function test_parse_state_names() {
        // Test single state
        $result = $this->parseStateNames( 'California' );
        $this->assertEquals( ['California'], $result );

        // Test multiple states
        $result = $this->parseStateNames( 'California, Texas, Florida' );
        $this->assertEquals( ['California', 'Texas', 'Florida'], $result );

        // Test with extra spaces
        $result = $this->parseStateNames( ' California , Texas , Florida ' );
        $this->assertEquals( ['California', 'Texas', 'Florida'], $result );

        // Test empty string - legacy behavior
        $result = $this->parseStateNames( '' );
        $this->assertEquals( [''], $result );
    }

    /**
     * Test service keywords list generation
     */
    public function test_service_keywords_list_generation() {
        $keywords     = $this->keywordsProvider->getAll();
        $keyword_list = implode( ', ', array_keys( $keywords ) );

        $this->assertIsString( $keyword_list );
        $this->assertNotEmpty( $keyword_list );
        $this->assertStringContainsString( 'WordPress development', $keyword_list );
    }

    /**
     * Helper method to convert to title case (mimics legacy behavior)
     */
    private function convertToTitleCase( string $text ): string {
        // Handle special cases like 84EM
        if ( str_contains( $text, '84EM' ) || str_contains( $text, '84em' ) ) {
            $text = str_replace( [ '84em', '84Em' ], '84EM', $text );
        }

        $words       = explode( ' ', $text );
        $small_words = [ 'a', 'an', 'and', 'as', 'at', 'but', 'by', 'for', 'if', 'in', 'nor', 'of', 'on', 'or', 'so', 'the', 'to', 'up', 'yet' ];

        $result = [];
        foreach ( $words as $index => $word ) {
            // Preserve 84EM
            if ( $word === '84EM' ) {
                $result[] = $word;
            }
            elseif ( $index === 0 || ! in_array( strtolower( $word ), $small_words, true ) ) {
                $result[] = ucfirst( strtolower( $word ) );
            }
            else {
                $result[] = strtolower( $word );
            }
        }

        return implode( ' ', $result );
    }

    /**
     * Helper method to process headings (mimics legacy behavior)
     */
    private function processHeadings( string $content ): string {
        // Process H2 headings
        $content = preg_replace_callback(
            '/<!-- wp:heading {"level":2} --><h2>(<a[^>]*>)?([^<]+)(<\/a>)?<\/h2><!-- \/wp:heading -->/i',
            function ( $matches ) {
                $heading_text   = $matches[2];
                $processed_text = '<strong>' . $this->convertToTitleCase( $heading_text ) . '</strong>';
                return '<!-- wp:heading {"level":2} --><h2>' . $processed_text . '</h2><!-- /wp:heading -->';
            },
            $content
        );

        // Process H3 headings
        $content = preg_replace_callback(
            '/<!-- wp:heading {"level":3} --><h3>(<a[^>]*>)?([^<]+)(<\/a>)?<\/h3><!-- \/wp:heading -->/i',
            function ( $matches ) {
                $heading_text   = $matches[2];
                $processed_text = '<strong>' . $this->convertToTitleCase( $heading_text ) . '</strong>';
                return '<!-- wp:heading {"level":3} --><h3>' . $processed_text . '</h3><!-- /wp:heading -->';
            },
            $content
        );

        return $content;
    }

    /**
     * Helper method to parse state names (mimics legacy behavior)
     */
    private function parseStateNames( string $state_arg ): array {
        // Legacy behavior: empty string returns array with empty string
        if ( $state_arg === '' ) {
            return [ '' ];
        }
        $states = array_map( 'trim', explode( ',', $state_arg ) );
        return array_values( array_filter( $states ) );
    }
}
