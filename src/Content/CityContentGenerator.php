<?php
/**
 * City Content Generator
 *
 * @package EightyFourEM\LocalPages\Content
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
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
     * @param  ClaudeApiClient  $apiClient  Claude API client
     * @param  StatesProvider  $statesProvider  States data provider
     * @param  KeywordsProvider  $keywordsProvider  Keywords provider
     * @param  SchemaGenerator  $schemaGenerator  Schema generator
     * @param  ContentProcessor  $contentProcessor  Content processor
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

        $apiClient   = $this->apiClient;
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
                'post_type'   => 'local',
                'meta_query'  => [
                    'relation' => 'AND',
                    [
                        'key'     => '_local_page_state',
                        'value'   => $state,
                        'compare' => '='
                    ],
                    [
                        'key'     => '_local_page_city',
                        'compare' => 'NOT EXISTS'
                    ]
                ],
                'numberposts' => 1,
                'post_status' => 'any',
            ] );

            $parent_id = ! empty( $state_posts ) ? $state_posts[0]->ID : 0;

            // Create city slug
            $city_slug = sanitize_title( $city );

            // Create post
            $post_data = [
                'post_title'   => $this->getPostTitle( "$city, $state" ),
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
                    '_genesis_description'  => $this->getMetaDescription( "{$city}, {$state}" ),
                    '_genesis_title'        => $this->getPostTitle( "{$city}, {$state}" ),
                ],
            ];

            $post_id = wp_insert_post( $post_data );

            if ( is_wp_error( $post_id ) ) {
                throw new Exception( 'Failed to create post: ' . $post_id->get_error_message() );
            }

            // Generate and save schema
            $schema = $this->schemaGenerator->generateCitySchema( $state, $city );
            update_post_meta( $post_id, 'schema', $schema );

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
                'post_title'    => $this->getPostTitle( "$city, $state" ),
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
            update_post_meta( $post_id, '_genesis_description', $this->getMetaDescription( "{$city}, {$state}" ) );
            update_post_meta( $post_id, '_genesis_title', $this->getPostTitle( "$city, $state" ) );

            // Regenerate schema
            $schema = $this->schemaGenerator->generateCitySchema( $state, $city );
            update_post_meta( $post_id, 'schema', $schema );

            return true;

        } catch ( Exception $e ) {
            WP_CLI::error( "Failed to update city page for {$city}, {$state}  (ID: {$post_id}): " . $e->getMessage() );
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
     * @param  string  $data
     *
     * @param  string|null  $cities
     *
     * @return string
     */
    public function getMetaDescription( string $data, string $cities = null ): string {

        return "Professional WordPress development, custom plugins, and web solutions for businesses in {$data}. White-label services and expert support.";
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

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely. But DO mention that 84EM is headquartered in Cedar Rapids, Iowa. No need to specifically use the phrase \"remote-first\".

CONTENT STRUCTURE (REQUIRED):

**Opening Section (3-4 short sentences, one per line)**
- Professional introduction mentioning {$city}, {$state} and local business context
- Brief overview of 84EM's WordPress expertise
- Include ONE contextual call-to-action link in the opening

**Core Services Section (H2: \"WordPress Development Services in {$city}\")**
Present services in an UNORDERED LIST using WordPress block syntax:
<!-- wp:list -->
<ul>
<li>Service name with brief 5-8 word benefit-focused description</li>
<li>Service name with brief 5-8 word benefit-focused description</li>
</ul>
<!-- /wp:list -->

Include these services from the list: {$service_keywords_list}
Select 6-8 most relevant services and present as list items. Keep descriptions concise and focused on business benefits, NOT keyword-stuffed.

**Why Choose 84EM Section (H2: \"Why {$city} Businesses Choose 84EM\")**
Present 3-4 key benefits as an UNORDERED LIST:
<!-- wp:list -->
<ul>
<li>Fully remote team serving clients nationwide with proven processes</li>
<li>30 years of combined WordPress development experience</li>
<li>Proven track record across diverse industries</li>
<li>Scalable solutions designed to grow with your business</li>
</ul>
<!-- /wp:list -->

**Closing Paragraph**
- 2 sentences, each on their own line, emphasizing local relevance and 84EM's headquarters in Cedar Rapids, Iowa
- Strong call-to-action with contact link
- Mention web development in {$state}

IMPORTANT GRAMMAR RULES:
- Use proper prepositions (in, for, near) when mentioning locations
- Never use city/state names as adjectives directly before service terms (avoid \"{$city} solutions\")
- Correct: \"businesses in {$city}\", \"services for {$city} companies\", \"development in {$city}\"
- Incorrect: \"{$city} businesses seeking {$city} solutions\"

TARGET METRICS:
- Total word count: 200-300 words
- Opening: 3-4 short sentences, each on their own line
- Services: 6-8 list items with brief descriptions
- Benefits: 3-4 list items
- Closing: 2 sentences, each on their own line
- Call-to-action links: 2-3 total (contextual, not in lists)

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
}
