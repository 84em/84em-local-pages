<?php
/**
 * City Content Generator
 *
 * @package EightyFourEM\LocalPages\Content
 */

namespace EightyFourEM\LocalPages\Content;

use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
use EightyFourEM\LocalPages\Schema\SchemaGenerator;
use EightyFourEM\LocalPages\Utils\ContentProcessor;
use EightyFourEM\LocalPages\Contracts\ContentGeneratorInterface;
use WP_CLI;
use Exception;

/**
 * Generates content for city-specific local pages
 */
class CityContentGenerator implements ContentGeneratorInterface {
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
     * Keywords provider
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
     * @param  ApiKeyManager  $apiKeyManager  API key manager
     * @param  StatesProvider  $statesProvider  States data provider
     * @param  KeywordsProvider  $keywordsProvider  Keywords provider
     * @param  SchemaGenerator  $schemaGenerator  Schema generator
     * @param  ContentProcessor  $contentProcessor  Content processor
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
     * Generate content for a city page
     *
     * @param  array  $data  Data for content generation
     *
     * @return string Generated content
     * @throws Exception If generation fails
     */
    public function generate( array $data ): string {
        if ( ! $this->validate( $data ) ) {
            throw new Exception( 'Invalid data provided for city content generation' );
        }

        $state  = $data['state'];
        $city   = $data['city'];
        $prompt = $this->buildPrompt( $state, $city );

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
        $processed_content = $this->contentProcessor->processContent(
            $raw_content,
            [ 'state' => $state, 'city' => $city ]
        );

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
        if ( ! isset( $data['state'] ) || ! isset( $data['city'] ) ) {
            return false;
        }

        $state = $data['state'];
        $city  = $data['city'];

        // Validate state exists
        if ( ! $this->statesProvider->has( $state ) ) {
            return false;
        }

        // Validate city exists in state
        $state_data = $this->statesProvider->get( $state );
        $cities     = $state_data['cities'] ?? [];

        return in_array( $city, $cities );
    }

    /**
     * Generate a complete city page
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return int|false Post ID on success, false on failure
     */
    public function generateCityPage( string $state, string $city ): int|false {
        try {
            WP_CLI::log( "🏙️ Generating content for {$city}, {$state}..." );

            $content = $this->generate( [ 'state' => $state, 'city' => $city ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Find or get state parent page
            $state_posts = get_posts( [
                'post_type'      => 'local',
                'meta_key'       => '_local_page_state',
                'meta_value'     => $state,
                'meta_compare'   => '=',
                'posts_per_page' => 1,
            ] );

            $parent_id = ! empty( $state_posts ) ? $state_posts[0]->ID : 0;

            // Create city slug
            $city_slug = sanitize_title( $city );

            // Create post
            $post_data = [
                'post_title'   => $sections['title'] ?? "WordPress Development Services in {$city}, {$state}",
                'post_name'    => $city_slug,
                'post_content' => $content,
                'post_excerpt' => $sections['excerpt'] ?? '',
                'post_status'  => 'publish',
                'post_type'    => 'local',
                'post_parent'  => $parent_id,
                'meta_input'   => [
                    '_local_page_type'      => 'city',
                    '_local_page_state'     => $state,
                    '_local_page_city'      => $city,
                    '_local_page_generated' => current_time( 'mysql' ),
                    '_yoast_wpseo_metadesc' => $sections['meta_description'] ?? '',
                    '_yoast_wpseo_title'    => $sections['seo_title'] ?? '',
                ],
            ];

            $post_id = wp_insert_post( $post_data );

            if ( is_wp_error( $post_id ) ) {
                throw new Exception( 'Failed to create post: ' . $post_id->get_error_message() );
            }

            // Generate and save schema
            $schema = $this->schemaGenerator->generateCitySchema( $state, $city );
            update_post_meta( $post_id, '_local_page_schema', $schema );

            WP_CLI::success( "Created city page: {$city}, {$state} (ID: {$post_id})" );

            return $post_id;

        } catch ( Exception $e ) {
            WP_CLI::error( "Failed to generate city page for {$city}, {$state}: " . $e->getMessage() );
            return false;
        }
    }

    /**
     * Update an existing city page
     *
     * @param  int  $post_id  Post ID to update
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return bool Success status
     */
    public function updateCityPage( int $post_id, string $state, string $city ): bool {
        try {
            WP_CLI::log( "🔄 Updating content for {$city}, {$state}..." );

            $content = $this->generate( [ 'state' => $state, 'city' => $city ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Update post
            $post_data = [
                'ID'            => $post_id,
                'post_content'  => $content,
                'post_excerpt'  => $sections['excerpt'] ?? '',
                'post_modified' => current_time( 'mysql' ),
            ];

            $result = wp_update_post( $post_data );

            if ( is_wp_error( $result ) ) {
                throw new Exception( 'Failed to update post: ' . $result->get_error_message() );
            }

            // Update metadata
            update_post_meta( $post_id, '_local_page_generated', current_time( 'mysql' ) );
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $sections['meta_description'] ?? '' );
            update_post_meta( $post_id, '_yoast_wpseo_title', $sections['seo_title'] ?? '' );

            // Regenerate schema
            $schema = $this->schemaGenerator->generateCitySchema( $state, $city );
            update_post_meta( $post_id, '_local_page_schema', $schema );

            WP_CLI::success( "Updated city page: {$city}, {$state}" );

            return true;

        } catch ( Exception $e ) {
            WP_CLI::error( "Failed to update city page for {$city}, {$state}: " . $e->getMessage() );
            return false;
        }
    }

    /**
     * Build the prompt for Claude API
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return string
     */
    private function buildPrompt( string $state, string $city ): string {
        // Get service keywords for the prompt
        $service_keywords      = $this->keywordsProvider->getAll();
        $service_keywords_list = implode( ', ', array_keys( $service_keywords ) );

        $prompt = "Write a concise, SEO-optimized landing page for 84EM's WordPress development services specifically for businesses in {$city}, {$state}.

IMPORTANT: Create unique, original content that is different from other city pages. Focus on local relevance through city-specific benefits and geographic context.

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely.

Include the following key elements:
1. A professional opening paragraph mentioning {$city}, {$state} and local business benefits
2. WordPress development services including: {$service_keywords_list}
3. Why businesses in {$city} choose 84EM (remote expertise, proven track record, reliable delivery)
4. Call-to-action for {$city} businesses
5. Include naturally-placed keywords: 'WordPress development in {$city}', 'custom plugins for {$city}', 'web development in {$state}'

IMPORTANT GRAMMAR RULES:
- Use proper prepositions (in, for, near) when mentioning locations
- Never use city/state names as adjectives directly before service terms (avoid "{$city} solutions")
- Correct: "businesses in {$city}", "services for {$city} companies", "development in {$city}"
- Incorrect: "{$city} businesses seeking {$city} solutions"

Write approximately 250-350 words in a professional, factual tone. Avoid hyperbole and superlatives. Focus on concrete services, technical expertise, and actual capabilities. Make it locally relevant through geographic references while emphasizing 84EM's remote-first approach serves clients nationwide.

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
}
