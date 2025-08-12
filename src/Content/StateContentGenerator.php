<?php
/**
 * State Content Generator
 *
 * @package EightyFourEM\LocalPages\Content
 */

namespace EightyFourEM\LocalPages\Content;

use EightyFourEM\LocalPages\Contracts\ContentGeneratorInterface;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
use EightyFourEM\LocalPages\Schema\SchemaGenerator;
use EightyFourEM\LocalPages\Utils\ContentProcessor;
use WP_CLI;
use Exception;

/**
 * Generates content for state pages using Claude API
 */
class StateContentGenerator implements ContentGeneratorInterface {

    /**
     * API key manager
     *
     * @var ApiKeyManager
     */
    private ApiKeyManager $apiKeyManager;

    /**
     * States data provider
     *
     * @var StatesProvider
     */
    private StatesProvider $statesProvider;

    /**
     * Keywords data provider
     *
     * @var KeywordsProvider
     */
    private KeywordsProvider $keywordsProvider;

    /**
     * Schema generator
     *
     * @var SchemaGenerator
     */
    private SchemaGenerator $schemaGenerator;

    /**
     * Content processor
     *
     * @var ContentProcessor
     */
    private ContentProcessor $contentProcessor;

    /**
     * Constructor
     *
     * @param  ApiKeyManager  $apiKeyManager
     * @param  StatesProvider  $statesProvider
     * @param  KeywordsProvider  $keywordsProvider
     * @param  SchemaGenerator  $schemaGenerator
     * @param  ContentProcessor  $contentProcessor
     */
    public function __construct(
        ApiKeyManager $apiKeyManager,
        StatesProvider $statesProvider,
        KeywordsProvider $keywordsProvider,
        SchemaGenerator $schemaGenerator,
        ContentProcessor $contentProcessor
    ) {
        $this->apiKeyManager    = $apiKeyManager;
        $this->statesProvider   = $statesProvider;
        $this->keywordsProvider = $keywordsProvider;
        $this->schemaGenerator  = $schemaGenerator;
        $this->contentProcessor = $contentProcessor;
    }

    /**
     * Generate content based on provided data
     *
     * @param  array  $data  Data for content generation
     *
     * @return string Generated content
     * @throws Exception
     */
    public function generate( array $data ): string {
        if ( ! $this->validate( $data ) ) {
            throw new Exception( 'Invalid data provided for state content generation' );
        }

        $state  = $data['state'];
        $prompt = $this->buildPrompt( $state );

        // Verify API key exists
        if ( ! $this->apiKeyManager->hasKey() ) {
            throw new Exception( 'API key not available' );
        }

        $apiClient   = new ClaudeApiClient( $this->apiKeyManager );
        $raw_content = $apiClient->sendRequest( $prompt );

        if ( ! $raw_content ) {
            throw new Exception( 'Failed to generate content from API' );
        }

        // Process the raw content
        $processed_content = $this->contentProcessor->processContent( $raw_content, [ 'state' => $state ] );

        return $processed_content;
    }

    /**
     * Validate that required data is present
     *
     * @param  array  $data  Data to validate
     *
     * @return bool
     */
    public function validate( array $data ): bool {
        if ( ! isset( $data['state'] ) ) {
            return false;
        }

        $state = $data['state'];
        return $this->statesProvider->has( $state );
    }

    /**
     * Generate a complete state page
     *
     * @param  string  $state  State name
     *
     * @return int|false Post ID on success, false on failure
     */
    public function generateStatePage( string $state ): int|false {
        try {
            WP_CLI::log( "🏛️ Generating content for {$state}..." );

            $content = $this->generate( [ 'state' => $state ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Validate content quality
            $validation = $this->contentProcessor->validateContent( $sections['content'] );
            if ( ! $validation['success'] ) {
                WP_CLI::warning( "Content quality issues for {$state}: " . implode( ', ', $validation['issues'] ) );
            }

            // Create the WordPress post
            $post_data = [
                'post_title'   => $sections['title'] ?: "WordPress Development Services in {$state}",
                'post_content' => $sections['content'],
                'post_excerpt' => $sections['excerpt'],
                'post_status'  => 'publish',
                'post_type'    => 'local',
                'post_author'  => 1,
                'meta_input'   => [
                    '_local_page_state'      => $state,
                    '_yoast_wpseo_metadesc'  => $sections['meta_description'],
                    '_yoast_wpseo_title'     => $sections['title'] ?: "WordPress Development Services in {$state}",
                    '_yoast_wpseo_canonical' => $this->generateStateUrl( $state ),
                ],
            ];

            $post_id = wp_insert_post( $post_data, true );

            if ( is_wp_error( $post_id ) ) {
                throw new Exception( 'Failed to create post: ' . $post_id->get_error_message() );
            }

            // Set up URL structure
            $this->setupStateUrl( $post_id, $state );

            WP_CLI::log( "✅ Generated state page for {$state} (ID: {$post_id})" );

            return $post_id;

        } catch ( Exception $e ) {
            WP_CLI::error( "Failed to generate state page for {$state}: " . $e->getMessage() );
            return false;
        }
    }

    /**
     * Update an existing state page
     *
     * @param  int  $post_id  Post ID to update
     * @param  string  $state  State name
     *
     * @return bool Success status
     */
    public function updateStatePage( int $post_id, string $state ): bool {
        try {
            WP_CLI::log( "🔄 Updating content for {$state}..." );

            $content = $this->generate( [ 'state' => $state ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Update the post
            $post_data = [
                'ID'            => $post_id,
                'post_title'    => $sections['title'] ?: "WordPress Development Services in {$state}",
                'post_content'  => $sections['content'],
                'post_excerpt'  => $sections['excerpt'],
                'post_modified' => current_time( 'mysql' ),
            ];

            $result = wp_update_post( $post_data, true );

            if ( is_wp_error( $result ) ) {
                throw new Exception( 'Failed to update post: ' . $result->get_error_message() );
            }

            // Update meta fields
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $sections['meta_description'] );
            update_post_meta( $post_id, '_yoast_wpseo_title', $sections['title'] ?: "WordPress Development Services in {$state}" );
            update_post_meta( $post_id, '_yoast_wpseo_canonical', $this->generateStateUrl( $state ) );

            WP_CLI::log( "✅ Updated state page for {$state} (ID: {$post_id})" );

            return true;

        } catch ( Exception $e ) {
            WP_CLI::error( "Failed to update state page for {$state}: " . $e->getMessage() );
            return false;
        }
    }

    /**
     * Build the prompt for Claude API
     *
     * @param  string  $state  State name
     *
     * @return string The prompt for API
     */
    private function buildPrompt( string $state ): string {
        // Get state data and cities
        $state_data = $this->statesProvider->get( $state );
        $cities     = $state_data['cities'] ?? [];
        $city_list  = implode( ', ', array_slice( $cities, 0, 6 ) );

        // Get service keywords for the prompt
        $service_keywords      = $this->keywordsProvider->getAll();
        $service_keywords_list = implode( ', ', array_keys( $service_keywords ) );

        $prompt = "Write a concise, SEO-optimized landing page for 84EM's WordPress development services specifically for businesses in {$state}.

IMPORTANT: Create unique, original content that is different from other state pages. Focus on local relevance through city mentions and state-specific benefits.

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely.

Include the following key elements:
1. A professional opening paragraph mentioning {$state} and cities like {$city_list}
2. WordPress development services including: {$service_keywords_list}
3. Why businesses in {$state} choose 84EM (30 years experience, diverse client industries, proven track record, reliable delivery)
4. Call-to-action for {$state} businesses
5. Include naturally-placed keywords: 'WordPress development {$state}', 'custom plugins {$state}', 'web development {$city_list}'

Write approximately 300-400 words in a professional, factual tone. Avoid hyperbole and superlatives. Focus on concrete services, technical expertise, and actual capabilities. Make it locally relevant through geographic references while emphasizing 84EM's remote-first approach serves clients nationwide.

CRITICAL: Format the content using WordPress block editor syntax (Gutenberg blocks). Use the following format:
- Paragraphs: <!-- wp:paragraph --><p>Your paragraph text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {\"level\":2} --><h2><strong>Your Heading</strong></h2><!-- /wp:heading -->
- Sub-headings: <!-- wp:heading {\"level\":3} --><h3><strong>Your Sub-heading</strong></h3><!-- /wp:heading -->
- Call-to-action links: <a href=\"/contact/\">contact us today</a> or <a href=\"/contact/\">get started</a>

IMPORTANT:
- All headings (h2, h3) must be wrapped in <strong> tags to ensure they appear bold.
- Include 2-3 call-to-action links throughout the content that link to /contact/ using phrases like \"contact us today\", \"get started\", \"reach out\", \"discuss your project\", etc.
- Make the call-to-action links natural and contextual within the content.
- Insert this exact CTA block BEFORE every H2 heading:

<!-- wp:group {\"className\":\"get-started-local\",\"style\":{\"spacing\":{\"margin\":{\"top\":\"0\"},\"padding\":{\"bottom\":\"var:preset|spacing|40\",\"top\":\"var:preset|spacing|40\",\"right\":\"0\"}}},\"layout\":{\"type\":\"constrained\",\"contentSize\":\"1280px\"}} -->
<div class=\"wp-block-group get-started-local\" style=\"margin-top:0;padding-top:var(--wp--preset--spacing--40);padding-right:0;padding-bottom:var(--wp--preset--spacing--40)\"><!-- wp:buttons {\"className\":\"animated bounceIn\",\"layout\":{\"type\":\"flex\",\"justifyContent\":\"center\"}} -->
<div class=\"wp-block-buttons animated bounceIn\"><!-- wp:button {\"style\":{\"border\":{\"radius\":{\"topLeft\":\"0px\",\"topRight\":\"30px\",\"bottomLeft\":\"30px\",\"bottomRight\":\"0px\"}},\"shadow\":\"var:preset|shadow|crisp\"},\"fontSize\":\"large\"} -->
<div class=\"wp-block-button\"><a class=\"wp-block-button__link has-large-font-size has-custom-font-size wp-element-button\" href=\"/contact/\" style=\"border-top-left-radius:0px;border-top-right-radius:30px;border-bottom-left-radius:30px;border-bottom-right-radius:0px;box-shadow:var(--wp--preset--shadow--crisp)\">Start Your WordPress Project</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

Do NOT use markdown syntax or plain HTML. Use proper WordPress block markup for all content.";

        return $prompt;
    }

    /**
     * Generate state page URL
     *
     * @param  string  $state  State name
     *
     * @return string State URL
     */
    private function generateStateUrl( string $state ): string {
        $slug = sanitize_title( $state );
        return home_url( "/wordpress-development-services-{$slug}/" );
    }

    /**
     * Set up URL structure for state page
     *
     * @param  int  $post_id  Post ID
     * @param  string  $state  State name
     *
     * @return void
     */
    private function setupStateUrl( int $post_id, string $state ): void {
        $slug         = sanitize_title( $state );
        $desired_slug = "wordpress-development-services-{$slug}";

        // Update post slug if needed
        $current_post = get_post( $post_id );
        if ( $current_post && $current_post->post_name !== $desired_slug ) {
            wp_update_post( [
                'ID'        => $post_id,
                'post_name' => $desired_slug,
            ] );
        }
    }

    /**
     * Get all generated state pages
     *
     * @return array Array of post objects
     */
    public function getAllStatePages(): array {
        return get_posts( [
            'post_type'   => 'local',
            'numberposts' => - 1,
            'post_status' => 'any',
            'meta_query'  => [
                [
                    'key'     => '_local_page_state',
                    'compare' => 'EXISTS',
                ],
                [
                    'key'     => '_local_page_city',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ] );
    }

    /**
     * Check if state page exists
     *
     * @param  string  $state  State name
     *
     * @return int|false Post ID if exists, false otherwise
     */
    public function statePageExists( string $state ): int|false {
        $posts = get_posts( [
            'post_type'   => 'local',
            'meta_key'    => '_local_page_state',
            'meta_value'  => $state,
            'numberposts' => 1,
            'post_status' => 'any',
            'meta_query'  => [
                [
                    'key'     => '_local_page_city',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ] );

        return ! empty( $posts ) ? $posts[0]->ID : false;
    }
}
