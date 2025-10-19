<?php
/**
 * State Content Generator
 *
 * @package EightyFourEM\LocalPages\Content
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
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
     * Claude API client
     *
     * @var ClaudeApiClient
     */
    private ClaudeApiClient $apiClient;

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
     * @param  ClaudeApiClient  $apiClient
     * @param  StatesProvider  $statesProvider
     * @param  KeywordsProvider  $keywordsProvider
     * @param  SchemaGenerator  $schemaGenerator
     * @param  ContentProcessor  $contentProcessor
     */
    public function __construct(
        ApiKeyManager $apiKeyManager,
        ClaudeApiClient $apiClient,
        StatesProvider $statesProvider,
        KeywordsProvider $keywordsProvider,
        SchemaGenerator $schemaGenerator,
        ContentProcessor $contentProcessor
    ) {
        $this->apiKeyManager    = $apiKeyManager;
        $this->apiClient        = $apiClient;
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

        $apiClient   = $this->apiClient;
        $raw_content = $apiClient->sendRequest( $prompt );

        if ( ! $raw_content ) {
            throw new Exception( 'Failed to generate content from API' );
        }

        // Get cities for this state to enable city interlinking
        $state_data = $this->statesProvider->get( $state );
        $cities = $state_data['cities'] ?? [];

        // Process the raw content with city context for interlinking
        $processed_content = $this->contentProcessor->processContent( $raw_content, [
            'state' => $state,
            'cities' => $cities
        ] );

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

            // Get cities for meta description
            $state_data = $this->statesProvider->get( $state );
            $cities     = $state_data['cities'] ?? [];

            // Create the WordPress post
            $post_data = [
                'post_title'   => $this->getPostTitle( $state ),
                'post_content' => $sections['content'],
                'post_excerpt' => $sections['excerpt'],
                'post_status'  => 'publish',
                'post_type'    => 'local',
                'post_author'  => 1,
                'meta_input'   => [
                    '_local_page_state'    => $state,
                    '_genesis_description' => $this->getMetaDescription( $state, implode( ', ', $cities ) ),
                    '_genesis_title'       => $this->getPostTitle( $state ),
                ],
            ];

            $post_id = wp_insert_post( $post_data, true );

            if ( is_wp_error( $post_id ) ) {
                throw new Exception( 'Failed to create post: ' . $post_id->get_error_message() );
            }

            // Set up URL structure
            $this->setupStateUrl( $post_id, $state );

            // Generate and save schema
            $schema = $this->schemaGenerator->generateStateSchema( $state );
            update_post_meta( $post_id, 'schema', $schema );

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
                'post_title'    => $this->getPostTitle( $state ),
                'post_content'  => $sections['content'],
                'post_excerpt'  => $sections['excerpt'],
                'post_modified' => current_time( 'mysql' ),
            ];

            $result = wp_update_post( $post_data, true );

            if ( is_wp_error( $result ) ) {
                throw new Exception( 'Failed to update post: ' . $result->get_error_message() );
            }

            // Get cities for meta description
            $state_data = $this->statesProvider->get( $state );
            $cities     = $state_data['cities'] ?? [];

            // Update meta fields
            update_post_meta( $post_id, '_genesis_description', $this->getMetaDescription( $state, implode( ', ', $cities ) ) );
            update_post_meta( $post_id, '_genesis_title', $this->getPostTitle( $state) );

            // Regenerate and save schema
            $schema = $this->schemaGenerator->generateStateSchema( $state );
            update_post_meta( $post_id, 'schema', $schema );

            WP_CLI::log( "✅ Updated state page for {$state} (ID: {$post_id})" );

            return true;

        } catch ( Exception $e ) {
            WP_CLI::error( "Failed to update state page for {$state}  (ID: {$post_id}): " . $e->getMessage() );
            return false;
        }
    }

    /**
     * Generate the post title based on the provided data.
     *
     * @param  mixed  $data  Input data used to construct the post title.
     *
     * @return string Generated post title.
     */
    public function getPostTitle( $data ): string {

        return "WordPress consulting & engineering, including custom plugins, security, enterprise integrations, and white‑label agency work in {$data} | 84EM";
    }

    /**
     * Generate the meta descirption based on the provided data.
     *
     * @param string $data
     * @param string|null $cities
     *
     * @return string
     */
    public function getMetaDescription( string $data, string $cities = null ): string {

        return "Professional WordPress development, custom plugins, and web solutions for businesses in {$data}. White-label services and expert support in {$cities}";
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

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely. But DO mention that 84EM is headquartered in Cedar Rapids, Iowa. No need to specifically use the phrase \"remote-first\".

CONTENT STRUCTURE (REQUIRED):

**Opening Section (1-2 short paragraphs)**
- Professional introduction mentioning {$state} and ALL of these cities: {$city_list} (you MUST mention all 6 cities naturally)
- Brief overview of 84EM's WordPress expertise
- Keep paragraphs to 2-3 sentences maximum
- Include ONE contextual call-to-action link in the opening

**Core Services Section (H2: \"WordPress Development Services in {$state}\")**
Present services in an UNORDERED LIST using WordPress block syntax:
<!-- wp:list -->
<ul>
<li>Service name with brief 5-8 word benefit-focused description</li>
<li>Service name with brief 5-8 word benefit-focused description</li>
</ul>
<!-- /wp:list -->

Include these services from the list: {$service_keywords_list}
Select 8-10 most relevant services and present as list items. Keep descriptions concise and focused on business benefits, NOT keyword-stuffed.

**Why Choose 84EM Section (H2: \"Why {$state} Businesses Choose 84EM\")**
Present 4-5 key benefits as an UNORDERED LIST:
<!-- wp:list -->
<ul>
<li>Fully remote team serving clients nationwide with proven processes</li>
<li>30 years of combined WordPress development experience</li>
<li>Proven track record across diverse industries</li>
<li>Reliable delivery with consistent communication</li>
<li>Scalable solutions designed to grow with your business</li>
</ul>
<!-- /wp:list -->

**Closing Paragraph**
- 2-3 sentences emphasizing local relevance across {$state} and 84EM's headquarters in Cedar Rapids, Iowa
- Strong call-to-action with contact link
- Mention several cities from the list: {$city_list}

IMPORTANT GRAMMAR RULES:
- Use proper prepositions (in, for, near) when mentioning locations
- Never use city/state names as adjectives directly before service terms (avoid \"{$state} solutions\")
- Correct: \"businesses in {$state}\", \"services for {$state} companies\", \"development in {$state}\"
- Incorrect: \"{$state} businesses seeking {$state} solutions\"

TARGET METRICS:
- Total word count: 300-400 words
- Opening: 1-2 paragraphs (2-3 sentences each)
- Services: 8-10 list items with brief descriptions
- Benefits: 4-5 list items
- Closing: 1 paragraph (2-3 sentences)
- Call-to-action links: 2-3 total (contextual, not in lists)
- City mentions: All 6 cities mentioned at least once

TONE: Professional and factual. Avoid hyperbole and superlatives. Focus on concrete services, technical expertise, and actual capabilities. Make it locally relevant through geographic references.

CRITICAL: Format the content using WordPress block editor syntax (Gutenberg blocks). Use the following format:
- Paragraphs: <!-- wp:paragraph --><p>Your paragraph text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {\"level\":2} --><h2><strong>Your Heading</strong></h2><!-- /wp:heading -->
- Lists: <!-- wp:list --><ul><li>Item text here</li><li>Item text here</li></ul><!-- /wp:list -->
- Call-to-action links: <a href=\"/contact/\">contact us today</a> or <a href=\"/contact/\">get started</a>

IMPORTANT:
- All headings (h2, h3) must be wrapped in <strong> tags to ensure they appear bold.
- Include 2-3 call-to-action links throughout the content that link to /contact/ using phrases like \"contact us today\", \"get started\", \"reach out\", \"discuss your project\", etc.
- Make the call-to-action links natural and contextual within PARAGRAPH content (not within list items).
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
}
