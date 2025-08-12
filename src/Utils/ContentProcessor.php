<?php
/**
 * Content Processor Utility
 *
 * @package EightyFourEM\LocalPages\Utils
 */

namespace EightyFourEM\LocalPages\Utils;

use EightyFourEM\LocalPages\Data\KeywordsProvider;

/**
 * Processes and enhances content with links, formatting, and other enhancements
 */
class ContentProcessor {

    /**
     * Keywords data provider
     *
     * @var KeywordsProvider
     */
    private KeywordsProvider $keywordsProvider;

    /**
     * Constructor
     *
     * @param  KeywordsProvider  $keywordsProvider
     */
    public function __construct( KeywordsProvider $keywordsProvider ) {
        $this->keywordsProvider = $keywordsProvider;
    }

    /**
     * Process content by adding internal links and formatting
     *
     * @param  string  $content  Raw content to process
     * @param  array  $context  Context data (state, city, etc.)
     *
     * @return string Processed content
     */
    public function processContent( string $content, array $context = [] ): string {
        // Clean up the content first
        $processed_content = $this->cleanContent( $content );

        // Add internal links to service keywords
        $processed_content = $this->addServiceLinks( $processed_content );

        // Add location-specific internal links
        $processed_content = $this->addLocationLinks( $processed_content, $context );

        // Format headings properly
        $processed_content = $this->formatHeadings( $processed_content );

        // Add WordPress blocks structure
        $processed_content = $this->addBlockStructure( $processed_content );

        return $processed_content;
    }

    /**
     * Clean up raw content from API
     *
     * @param  string  $content  Raw content
     *
     * @return string Cleaned content
     */
    private function cleanContent( string $content ): string {
        // Remove excessive whitespace
        $content = preg_replace( '/\s+/', ' ', $content );

        // Normalize line breaks
        $content = str_replace( [ "\r\n", "\r" ], "\n", $content );

        // Remove multiple consecutive line breaks
        $content = preg_replace( '/\n{3,}/', "\n\n", $content );

        // Trim each line
        $lines   = explode( "\n", $content );
        $lines   = array_map( 'trim', $lines );
        $content = implode( "\n", $lines );

        return trim( $content );
    }

    /**
     * Add internal links to service-related keywords
     *
     * @param  string  $content  Content to process
     *
     * @return string Content with service links added
     */
    private function addServiceLinks( string $content ): string {
        $service_keywords = $this->keywordsProvider->getAll();

        foreach ( $service_keywords as $keyword => $url ) {
            // Only link the first occurrence of each keyword
            $pattern = '/\b' . preg_quote( $keyword, '/' ) . '\b/i';

            // Check if this keyword already has a link
            if ( strpos( $content, '<a href="' . $url . '"' ) !== false ) {
                continue;
            }

            // Add the link to the first occurrence
            $content = preg_replace(
                $pattern,
                '<a href="' . esc_url( $url ) . '">' . $keyword . '</a>',
                $content,
                1 // Only replace first occurrence
            );
        }

        return $content;
    }

    /**
     * Add location-specific internal links
     *
     * @param  string  $content  Content to process
     * @param  array  $context  Context with state, city info
     *
     * @return string Content with location links added
     */
    private function addLocationLinks( string $content, array $context = [] ): string {
        $state = $context['state'] ?? null;
        $city  = $context['city'] ?? null;

        if ( $state ) {
            // Link to state page from city pages
            if ( $city ) {
                $state_url = $this->generateStateUrl( $state );
                $pattern   = '/\b' . preg_quote( $state, '/' ) . '\b(?![^<]*>)/';

                // Only add link if it doesn't already exist
                if ( strpos( $content, $state_url ) === false ) {
                    $content = preg_replace(
                        $pattern,
                        '<a href="' . esc_url( $state_url ) . '">' . $state . '</a>',
                        $content,
                        1
                    );
                }
            }
        }

        return $content;
    }

    /**
     * Format headings with proper HTML structure
     *
     * @param  string  $content  Content to process
     *
     * @return string Content with formatted headings
     */
    private function formatHeadings( string $content ): string {
        // Convert markdown-style headings to HTML
        $content = preg_replace( '/^### (.+)$/m', '<h3>$1</h3>', $content );
        $content = preg_replace( '/^## (.+)$/m', '<h2>$1</h2>', $content );
        $content = preg_replace( '/^# (.+)$/m', '<h1>$1</h1>', $content );

        // Ensure proper spacing around headings
        $content = preg_replace( '/(<h[1-6]>.*?<\/h[1-6]>)/', "\n\n$1\n\n", $content );

        // Clean up extra whitespace created
        $content = preg_replace( '/\n{3,}/', "\n\n", $content );

        return $content;
    }

    /**
     * Add WordPress block structure to content
     *
     * @param  string  $content  Content to process
     *
     * @return string Content with block structure
     */
    private function addBlockStructure( string $content ): string {
        $blocks     = [];
        $paragraphs = explode( "\n\n", $content );

        foreach ( $paragraphs as $paragraph ) {
            $paragraph = trim( $paragraph );
            if ( empty( $paragraph ) ) {
                continue;
            }

            // Check if this is a heading
            if ( preg_match( '/^<h([1-6])>(.*?)<\/h[1-6]>$/', $paragraph, $matches ) ) {
                $level    = $matches[1];
                $text     = $matches[2];
                $blocks[] = '<!-- wp:heading {"level":' . $level . '} -->';
                $blocks[] = '<h' . $level . '>' . $text . '</h' . $level . '>';
                $blocks[] = '<!-- /wp:heading -->';
            }
            else {
                // This is a paragraph
                $blocks[] = '<!-- wp:paragraph -->';
                $blocks[] = '<p>' . $paragraph . '</p>';
                $blocks[] = '<!-- /wp:paragraph -->';
            }

            $blocks[] = ''; // Add spacing between blocks
        }

        return implode( "\n", $blocks );
    }

    /**
     * Generate state page URL
     *
     * @param  string  $state  State name
     *
     * @return string State page URL
     */
    private function generateStateUrl( string $state ): string {
        $slug = sanitize_title( $state );
        return home_url( "/wordpress-development-services-{$slug}/" );
    }

    /**
     * Generate city page URL
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return string City page URL
     */
    public function generateCityUrl( string $state, string $city ): string {
        $state_slug = sanitize_title( $state );
        $city_slug  = sanitize_title( $city );
        return home_url( "/wordpress-development-services-{$state_slug}/{$city_slug}/" );
    }

    /**
     * Extract and validate content sections
     *
     * @param  string  $content  Raw content
     *
     * @return array Sections array with title, meta_description, content
     */
    public function extractContentSections( string $content ): array {
        $sections = [
            'title'            => '',
            'meta_description' => '',
            'content'          => '',
            'excerpt'          => '',
        ];

        // Try to extract title from the first heading
        if ( preg_match( '/^#+ (.+)$/m', $content, $matches ) ) {
            $sections['title'] = trim( $matches[1] );
        }

        // Try to extract meta description from content
        $first_paragraph = $this->getFirstParagraph( $content );
        if ( $first_paragraph ) {
            $sections['meta_description'] = $this->createMetaDescription( $first_paragraph );
            $sections['excerpt']          = wp_trim_words( $first_paragraph, 30 );
        }

        $sections['content'] = $content;

        return $sections;
    }

    /**
     * Get the first paragraph from content
     *
     * @param  string  $content  Content to analyze
     *
     * @return string First paragraph
     */
    private function getFirstParagraph( string $content ): string {
        // Remove headings and get first substantial paragraph
        $content_lines = explode( "\n", $content );

        foreach ( $content_lines as $line ) {
            $line = trim( $line );

            // Skip headings and empty lines
            if ( empty( $line ) || preg_match( '/^#+/', $line ) ) {
                continue;
            }

            // Found first paragraph
            if ( strlen( $line ) > 50 ) {
                return $line;
            }
        }

        return '';
    }

    /**
     * Create SEO-friendly meta description
     *
     * @param  string  $text  Source text
     *
     * @return string Meta description
     */
    private function createMetaDescription( string $text ): string {
        // Strip HTML tags
        $text = strip_tags( $text );

        // Limit to 155 characters for SEO
        $meta_description = wp_trim_words( $text, 25 );

        if ( strlen( $meta_description ) > 155 ) {
            $meta_description = substr( $meta_description, 0, 152 ) . '...';
        }

        return $meta_description;
    }

    /**
     * Validate content quality
     *
     * @param  string  $content  Content to validate
     *
     * @return array Validation result with success boolean and issues array
     */
    public function validateContent( string $content ): array {
        $issues = [];

        // Check minimum length
        $word_count = str_word_count( strip_tags( $content ) );
        if ( $word_count < 300 ) {
            $issues[] = "Content too short: {$word_count} words (minimum 300)";
        }

        // Check for headings
        if ( ! preg_match( '/<h[1-6]>|^#+/', $content ) ) {
            $issues[] = 'No headings found in content';
        }

        // Check for paragraph structure
        $paragraph_count = substr_count( $content, '<p>' ) + substr_count( $content, "\n\n" );
        if ( $paragraph_count < 3 ) {
            $issues[] = 'Content lacks proper paragraph structure';
        }

        return [
            'success'    => empty( $issues ),
            'issues'     => $issues,
            'word_count' => $word_count,
        ];
    }

    /**
     * Clean and format text for display
     *
     * @param  string  $text  Text to clean
     *
     * @return string Cleaned text
     */
    public function cleanText( string $text ): string {
        // Remove HTML tags
        $text = strip_tags( $text );

        // Normalize whitespace
        $text = preg_replace( '/\s+/', ' ', $text );

        // Trim
        return trim( $text );
    }
}
