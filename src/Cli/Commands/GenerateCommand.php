<?php
/**
 * Generate Command Handler
 *
 * @package EightyFourEM\LocalPages\Cli\Commands
 */

namespace EightyFourEM\LocalPages\Cli\Commands;

use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
use EightyFourEM\LocalPages\Content\StateContentGenerator;
use EightyFourEM\LocalPages\Content\CityContentGenerator;
use EightyFourEM\LocalPages\Utils\ContentProcessor;
use EightyFourEM\LocalPages\Schema\SchemaGenerator;
use WP_CLI;
use Exception;

/**
 * Handles all generation-related CLI commands
 */
class GenerateCommand {

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
     * State content generator
     *
     * @var StateContentGenerator
     */
    private StateContentGenerator $stateContentGenerator;

    /**
     * City content generator
     *
     * @var CityContentGenerator
     */
    private CityContentGenerator $cityContentGenerator;

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
     */
    public function __construct(
        ApiKeyManager $apiKeyManager,
        StatesProvider $statesProvider,
        KeywordsProvider $keywordsProvider
    ) {
        $this->apiKeyManager    = $apiKeyManager;
        $this->statesProvider   = $statesProvider;
        $this->keywordsProvider = $keywordsProvider;

        // Initialize content processors and generators
        $this->contentProcessor = new ContentProcessor( $keywordsProvider );
        $schemaGenerator        = new SchemaGenerator( $statesProvider );

        $this->stateContentGenerator = new StateContentGenerator(
            $apiKeyManager,
            $statesProvider,
            $keywordsProvider,
            $schemaGenerator,
            $this->contentProcessor
        );
        $this->cityContentGenerator  = new CityContentGenerator(
            $apiKeyManager,
            $statesProvider,
            $keywordsProvider,
            $schemaGenerator,
            $this->contentProcessor
        );
    }

    /**
     * Handle generate-all command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleGenerateAll( array $args, array $assoc_args ): void {
        $include_cities = ! isset( $assoc_args['states-only'] );

        WP_CLI::line( '🚀 Starting comprehensive generation process...' );
        WP_CLI::line( '' );

        if ( $include_cities ) {
            WP_CLI::line( '📊 This will generate/update:' );
            WP_CLI::line( '   • 50 state pages' );
            WP_CLI::line( '   • 300 city pages (6 per state)' );
            WP_CLI::line( '   • Total: 350 pages' );
        }
        else {
            WP_CLI::line( '📊 This will generate/update 50 state pages only.' );
        }

        WP_CLI::line( '' );

        $states_data  = $this->statesProvider->getAll();
        $total_states = count( $states_data );

        $state_created_count = 0;
        $state_updated_count = 0;
        $city_created_count  = 0;
        $city_updated_count  = 0;

        // Initialize progress bar for states
        $progress = \WP_CLI\Utils\make_progress_bar( 'Processing all states and cities', $total_states );

        foreach ( $states_data as $state => $data ) {
            WP_CLI::log( "🏛️ Processing {$state}..." );

            // Generate/update state page first
            $existing_state_posts = get_posts( [
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

            if ( ! empty( $existing_state_posts ) ) {
                if ( $this->stateContentGenerator->updateStatePage( $existing_state_posts[0]->ID, $state ) ) {
                    $state_updated_count ++;
                    WP_CLI::log( "  ✅ Updated state page: {$state} (ID: {$existing_state_posts[0]->ID})" );
                }
                else {
                    WP_CLI::warning( "  ❌ Failed to update state page: {$state} (ID: {$existing_state_posts[0]->ID})" );
                }
            }
            else {
                $post_id = $this->stateContentGenerator->generateStatePage( $state );
                if ( $post_id ) {
                    $state_created_count ++;
                    WP_CLI::log( "  ✅ Created state page: {$state}" );
                }
                else {
                    WP_CLI::warning( "  ❌ Failed to create state page: {$state}" );
                }
            }

            // Generate cities if requested
            if ( $include_cities ) {
                $cities = $data['cities'] ?? [];
                foreach ( $cities as $city ) {
                    // Check if city page exists
                    $existing_city_posts = get_posts( [
                        'post_type'   => 'local',
                        'meta_query'  => [
                            'relation' => 'AND',
                            [
                                'key'   => '_local_page_state',
                                'value' => $state,
                            ],
                            [
                                'key'   => '_local_page_city',
                                'value' => $city,
                            ],
                        ],
                        'numberposts' => 1,
                        'post_status' => 'any',
                    ] );

                    if ( ! empty( $existing_city_posts ) ) {
                        if ( $this->cityContentGenerator->updateCityPage( $existing_city_posts[0]->ID, $state, $city ) ) {
                            $city_updated_count ++;
                            WP_CLI::log( "    ✅ Updated city page: {$city}, {$state} (ID: {$existing_city_posts[0]->ID})" );;
                        }
                        else {
                            WP_CLI::warning( "    ❌ Failed to update city page: {$city}, {$state} (ID: {$existing_city_posts[0]->ID})" );
                        }
                    }
                    else {
                        $post_id = $this->cityContentGenerator->generateCityPage( $state, $city );
                        if ( $post_id ) {
                            $city_created_count ++;
                            WP_CLI::log( "    ✅ Created city page: {$city}, {$state} (ID: {$post_id})" );
                        }
                        else {
                            WP_CLI::warning( "    ❌ Failed to create city page: {$city}, {$state}" );
                        }
                    }

                    // Add delay between API requests to respect rate limits
                    sleep( 2 );
                }
            }

            $progress->tick();

            // Add delay between states to respect rate limits
            sleep( 2 );
        }

        $progress->finish();

        // Display final summary
        WP_CLI::line( '' );
        WP_CLI::line( '🎉 Generation Complete!' );
        WP_CLI::line( '======================' );
        WP_CLI::line( "States created: {$state_created_count}" );
        WP_CLI::line( "States updated: {$state_updated_count}" );

        if ( $include_cities ) {
            WP_CLI::line( "Cities created: {$city_created_count}" );
            WP_CLI::line( "Cities updated: {$city_updated_count}" );
            WP_CLI::line( "Total pages processed: " . ( $state_created_count + $state_updated_count + $city_created_count + $city_updated_count ) );
        }
        else {
            WP_CLI::line( "Total state pages processed: " . ( $state_created_count + $state_updated_count ) );
        }

        WP_CLI::success( 'All local pages have been generated/updated successfully!' );
    }

    /**
     * Handle update-all command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleUpdateAll( array $args, array $assoc_args ): void {
        WP_CLI::line( '🔄 Starting update of all existing local pages...' );
        WP_CLI::line( '' );

        $all_local_posts = get_posts( [
            'post_type'   => 'local',
            'numberposts' => - 1,
            'post_status' => 'any',
        ] );

        if ( empty( $all_local_posts ) ) {
            WP_CLI::warning( 'No local pages found to update.' );
            return;
        }

        $updated_count = 0;
        $failed_count  = 0;

        $progress = \WP_CLI\Utils\make_progress_bar( 'Updating local pages', count( $all_local_posts ) );

        foreach ( $all_local_posts as $post ) {
            $state = get_post_meta( $post->ID, '_local_page_state', true );
            $city  = get_post_meta( $post->ID, '_local_page_city', true );

            try {
                if ( $city ) {
                    // This is a city page
                    if ( $this->cityContentGenerator->updateCityPage( $post->ID, $state, $city ) ) {
                        $updated_count ++;
                        WP_CLI::log( "✅ Updated: {$city}, {$state}" );
                    }
                    else {
                        $failed_count ++;
                        WP_CLI::warning( "❌ Failed to update: {$city}, {$state}" );
                    }
                }
                else {
                    // This is a state page
                    if ( $this->stateContentGenerator->updateStatePage( $post->ID, $state ) ) {
                        $updated_count ++;
                        WP_CLI::log( "✅ Updated: {$state}" );
                    }
                    else {
                        $failed_count ++;
                        WP_CLI::warning( "❌ Failed to update: {$state}" );
                    }
                }

                // Add delay between API requests
                sleep( 2 );

            } catch ( Exception $e ) {
                $failed_count ++;
                $location = $city ? "{$city}, {$state}" : $state;
                WP_CLI::warning( "❌ Error updating {$location}: " . $e->getMessage() );
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::line( '' );
        WP_CLI::line( '📊 Update Summary' );
        WP_CLI::line( '=================' );
        WP_CLI::line( "Successfully updated: {$updated_count}" );
        WP_CLI::line( "Failed to update: {$failed_count}" );
        WP_CLI::line( "Total processed: " . count( $all_local_posts ) );

        if ( $failed_count === 0 ) {
            WP_CLI::success( 'All local pages updated successfully!' );
        }
        else {
            WP_CLI::warning( "Update completed with {$failed_count} failures." );
        }
    }

    /**
     * Handle state-specific command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleState( array $args, array $assoc_args ): void {
        $state_arg = $assoc_args['state'];

        // Handle 'all' states
        if ( $state_arg === 'all' ) {
            $this->handleGenerateAll( $args, $assoc_args );
            return;
        }

        $state_names   = $this->parseStateNames( $state_arg );
        $created_count = 0;
        $updated_count = 0;

        foreach ( $state_names as $state_name ) {
            // Validate state name
            if ( ! $this->statesProvider->has( $state_name ) ) {
                WP_CLI::warning( "Invalid state name: {$state_name}. Skipping." );
                continue;
            }

            // Check if page already exists (state page, not city)
            $existing_posts = get_posts( [
                'post_type'   => 'local',
                'meta_query'  => [
                    'relation' => 'AND',
                    [
                        'key'     => '_local_page_state',
                        'value'   => $state_name,
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

            if ( ! empty( $existing_posts ) ) {
                if ( $this->stateContentGenerator->updateStatePage( $existing_posts[0]->ID, $state_name ) ) {
                    $updated_count ++;
                    WP_CLI::success( "Updated state page: {$state_name} (ID: {$existing_posts[0]->ID})" );;
                }
                else {
                    WP_CLI::error( "Failed to update state page: {$state_name} (ID: {$existing_posts[0]->ID})" );
                }
            }
            else {
                $post_id = $this->stateContentGenerator->generateStatePage( $state_name );
                if ( $post_id ) {
                    $created_count ++;
                    WP_CLI::success( "Created state page: {$state_name} (ID: {$post_id})" );
                }
                else {
                    WP_CLI::error( "Failed to create state page: {$state_name}" );
                }
            }

            // Add delay between requests
            sleep( 2 );
        }

        WP_CLI::line( '' );
        WP_CLI::line( "📊 Summary: Created {$created_count}, Updated {$updated_count}" );
    }

    /**
     * Handle city-specific command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleCity( array $args, array $assoc_args ): void {
        $state_arg = $assoc_args['state'] ?? null;
        $city_arg  = $assoc_args['city'] ?? null;

        if ( empty( $state_arg ) ) {
            WP_CLI::error( 'State is required when working with cities. Use --state="State Name"' );
            return;
        }

        if ( empty( $city_arg ) ) {
            WP_CLI::error( 'City is required. Use --city="City Name" or --city=all' );
            return;
        }

        // Validate state name
        if ( ! $this->statesProvider->has( $state_arg ) ) {
            WP_CLI::error( "Invalid state name: {$state_arg}" );
            return;
        }

        // Handle 'all' cities for a state
        if ( $city_arg === 'all' ) {
            // Check if --complete flag is set to also update state page
            $complete = isset( $assoc_args['complete'] );
            $this->generateAllCitiesForState( $state_arg, $complete );
            return;
        }

        // Handle specific cities
        $city_names    = $this->parseCityNames( $city_arg );
        $created_count = 0;
        $updated_count = 0;

        foreach ( $city_names as $city_name ) {
            // Validate city is in state
            $state_data = $this->statesProvider->get( $state_arg );
            $cities     = $state_data['cities'] ?? [];

            if ( ! in_array( $city_name, $cities ) ) {
                WP_CLI::warning( "City '{$city_name}' not found in {$state_arg}. Skipping." );
                continue;
            }

            // Check if city page exists
            $existing_posts = get_posts( [
                'post_type'   => 'local',
                'meta_query'  => [
                    'relation' => 'AND',
                    [
                        'key'   => '_local_page_state',
                        'value' => $state_arg,
                    ],
                    [
                        'key'   => '_local_page_city',
                        'value' => $city_name,
                    ],
                ],
                'numberposts' => 1,
                'post_status' => 'any',
            ] );

            if ( ! empty( $existing_posts ) ) {
                if ( $this->cityContentGenerator->updateCityPage( $existing_posts[0]->ID, $state_arg, $city_name ) ) {
                    $updated_count ++;
                    WP_CLI::success( "Updated city page: {$city_name}, {$state_arg} (ID: {$existing_posts[0]->ID})" );
                }
                else {
                    WP_CLI::error( "Failed to update city page: {$city_name}, {$state_arg} (ID: {$existing_posts[0]->ID})" );
                }
            }
            else {
                $post_id = $this->cityContentGenerator->generateCityPage( $state_arg, $city_name );
                if ( $post_id ) {
                    $created_count ++;
                    WP_CLI::success( "Created city page: {$city_name}, {$state_arg} (ID: {$post_id})" );
                }
                else {
                    WP_CLI::error( "Failed to create city page: {$city_name}, {$state_arg}" );
                }
            }

            // Add delay between requests
            sleep( 2 );
        }

        WP_CLI::line( '' );
        WP_CLI::line( "📊 Summary: Created {$created_count}, Updated {$updated_count}" );
    }

    /**
     * Handle update command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleUpdate( array $args, array $assoc_args ): void {
        // This method can be extended to handle specific update scenarios
        // For now, delegate to update-all
        $this->handleUpdateAll( $args, $assoc_args );
    }

    /**
     * Handle delete command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleDelete( array $args, array $assoc_args ): void {
        $state_arg = $assoc_args['state'] ?? null;
        $city_arg  = $assoc_args['city'] ?? null;

        if ( empty( $state_arg ) ) {
            WP_CLI::error( 'State is required for delete operations. Use --state="State Name"' );
            return;
        }

        if ( $city_arg ) {
            $this->deleteCityPage( $state_arg, $city_arg );
        }
        else {
            $this->deleteStatePage( $state_arg );
        }
    }

    /**
     * Handle sitemap generation
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleSitemapGeneration( array $args, array $assoc_args ): void {
        WP_CLI::line( '🗺️ Generating XML sitemap for local pages...' );

        // This would integrate with the sitemap generation functionality
        // For now, provide a placeholder
        WP_CLI::warning( 'Sitemap generation functionality needs to be implemented.' );
    }

    /**
     * Handle index page generation
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleIndexGeneration( array $args, array $assoc_args ): void {
        WP_CLI::line( '📄 Generating index page for all locations...' );

        // This would integrate with the index generation functionality
        // For now, provide a placeholder
        WP_CLI::warning( 'Index page generation functionality needs to be implemented.' );
    }

    /**
     * Handle schema regeneration
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleSchemaRegeneration( array $args, array $assoc_args ): void {
        $state_filter = $assoc_args['state'] ?? null;
        $city_filter  = $assoc_args['city'] ?? null;
        $states_only  = isset( $assoc_args['states-only'] ) || isset( $assoc_args['state-only'] );

        // Build query args
        $query_args = [
            'post_type'   => 'local',
            'post_status' => 'any',
            'numberposts' => -1,
            'meta_query'  => [
                [
                    'key'     => '_local_page_state',
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        // Filter by specific state if provided
        if ( $state_filter ) {
            $query_args['meta_query'][] = [
                'key'   => '_local_page_state',
                'value' => $state_filter,
            ];
        }

        // Filter by specific city if provided
        if ( $city_filter ) {
            $query_args['meta_query'][] = [
                'key'   => '_local_page_city',
                'value' => $city_filter,
            ];
        }
        // If states-only flag is set, exclude city pages
        elseif ( $states_only ) {
            $query_args['meta_query'][] = [
                'key'     => '_local_page_city',
                'compare' => 'NOT EXISTS',
            ];
        }

        $posts = get_posts( $query_args );

        if ( empty( $posts ) ) {
            WP_CLI::warning( 'No local pages found to regenerate schema for.' );
            return;
        }

        $total = count( $posts );
        WP_CLI::line( "🔧 Regenerating schema markup for {$total} pages..." );

        $progress = \WP_CLI\Utils\make_progress_bar( 'Regenerating schemas', $total );
        $success_count = 0;
        $error_count = 0;

        // Get schema generator
        $schemaGenerator = new SchemaGenerator( $this->statesProvider );

        foreach ( $posts as $post ) {
            $state = get_post_meta( $post->ID, '_local_page_state', true );
            $city  = get_post_meta( $post->ID, '_local_page_city', true );

            try {
                if ( $city ) {
                    // Generate city schema
                    $schema = $schemaGenerator->generateCitySchema( $state, $city );
                } else {
                    // Generate state schema
                    $schema = $schemaGenerator->generateStateSchema( $state );
                }

                // Update the schema meta
                update_post_meta( $post->ID, 'schema', $schema );
                $success_count++;

            } catch ( \Exception $e ) {
                WP_CLI::warning( "Failed to regenerate schema for {$post->post_title}: " . $e->getMessage() );
                $error_count++;
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::line( '' );
        WP_CLI::line( '📊 Schema Regeneration Summary' );
        WP_CLI::line( '==============================' );
        WP_CLI::line( "Successfully regenerated: {$success_count}" );
        WP_CLI::line( "Failed: {$error_count}" );
        WP_CLI::line( "Total processed: {$total}" );

        if ( $error_count === 0 ) {
            WP_CLI::success( 'All schemas regenerated successfully!' );
        } else {
            WP_CLI::warning( "Schema regeneration completed with {$error_count} errors." );
        }
    }

    /**
     * Generate all cities for a specific state
     *
     * @param  string  $state  State name
     *
     * @return void
     */
    private function generateAllCitiesForState( string $state, bool $update_state_page = false ): void {
        $state_data = $this->statesProvider->get( $state );
        if ( ! $state_data ) {
            WP_CLI::error( "Invalid state: {$state}" );
            return;
        }

        $cities = $state_data['cities'] ?? [];
        if ( empty( $cities ) ) {
            WP_CLI::warning( "No cities defined for {$state}" );
            return;
        }

        WP_CLI::line( "🏙️ Generating all cities for {$state}..." );

        $created_count = 0;
        $updated_count = 0;

        $progress = \WP_CLI\Utils\make_progress_bar( "Processing {$state} cities", count( $cities ) );

        foreach ( $cities as $city ) {
            // Check if city page exists
            $existing_posts = get_posts( [
                'post_type'   => 'local',
                'meta_query'  => [
                    'relation' => 'AND',
                    [
                        'key'   => '_local_page_state',
                        'value' => $state,
                    ],
                    [
                        'key'   => '_local_page_city',
                        'value' => $city,
                    ],
                ],
                'numberposts' => 1,
                'post_status' => 'any',
            ] );

            if ( ! empty( $existing_posts ) ) {
                if ( $this->cityContentGenerator->updateCityPage( $existing_posts[0]->ID, $state, $city ) ) {
                    $updated_count ++;
                }
                else {
                    WP_CLI::warning( "Failed to update: {$city}, {$state}" );
                }
            }
            else {
                $post_id = $this->cityContentGenerator->generateCityPage( $state, $city );
                if ( $post_id ) {
                    $created_count ++;
                }
                else {
                    WP_CLI::warning( "Failed to create: {$city}, {$state}" );
                }
            }

            $progress->tick();
            sleep( 2 );
        }

        $progress->finish();

        WP_CLI::line( '' );
        WP_CLI::success( "Completed {$state} cities: Created {$created_count}, Updated {$updated_count}" );

        // If requested, also update the state page
        if ( $update_state_page ) {
            WP_CLI::line( '' );
            WP_CLI::line( "🏛️ Now updating {$state} state page..." );

            // Check if state page exists
            $existing_state_posts = get_posts( [
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

            if ( ! empty( $existing_state_posts ) ) {
                if ( $this->stateContentGenerator->updateStatePage( $existing_state_posts[0]->ID, $state ) ) {
                    WP_CLI::success( "Updated state page: {$state} (ID: {$existing_state_posts[0]->ID})" );;
                }
                else {
                    WP_CLI::error( "Failed to update state page: {$state} (ID: {$existing_state_posts[0]->ID})" );;
                }
            }
            else {
                $post_id = $this->stateContentGenerator->generateStatePage( $state );
                if ( $post_id ) {
                    WP_CLI::success( "Created state page: {$state} (ID: {$post_id})" );
                }
                else {
                    WP_CLI::error( "Failed to create state page: {$state}" );
                }
            }

            sleep( 2 );
        }
    }

    /**
     * Delete a city page
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return void
     */
    private function deleteCityPage( string $state, string $city ): void {
        $posts = get_posts( [
            'post_type'   => 'local',
            'meta_query'  => [
                'relation' => 'AND',
                [
                    'key'   => '_local_page_state',
                    'value' => $state,
                ],
                [
                    'key'   => '_local_page_city',
                    'value' => $city,
                ],
            ],
            'numberposts' => 1,
            'post_status' => 'any',
        ] );

        if ( empty( $posts ) ) {
            WP_CLI::warning( "City page not found: {$city}, {$state}" );
            return;
        }

        if ( wp_delete_post( $posts[0]->ID, true ) ) {
            WP_CLI::success( "Deleted city page: {$city}, {$state}" );
        }
        else {
            WP_CLI::error( "Failed to delete city page: {$city}, {$state}" );
        }
    }

    /**
     * Delete a state page and all its cities
     *
     * @param  string  $state  State name
     *
     * @return void
     */
    private function deleteStatePage( string $state ): void {
        // Find all pages for this state (state page + city pages)
        $posts = get_posts( [
            'post_type'   => 'local',
            'meta_key'    => '_local_page_state',
            'meta_value'  => $state,
            'numberposts' => - 1,
            'post_status' => 'any',
        ] );

        if ( empty( $posts ) ) {
            WP_CLI::warning( "No pages found for state: {$state}" );
            return;
        }

        $deleted_count = 0;
        foreach ( $posts as $post ) {
            if ( wp_delete_post( $post->ID, true ) ) {
                $deleted_count ++;
            }
        }

        WP_CLI::success( "Deleted {$deleted_count} pages for state: {$state}" );
    }

    /**
     * Parse comma-separated state names
     *
     * @param  string  $states_string  Comma-separated state names
     *
     * @return array
     */
    private function parseStateNames( string $states_string ): array {
        return array_map( 'trim', explode( ',', $states_string ) );
    }

    /**
     * Parse comma-separated city names
     *
     * @param  string  $cities_string  Comma-separated city names
     *
     * @return array
     */
    private function parseCityNames( string $cities_string ): array {
        return array_map( 'trim', explode( ',', $cities_string ) );
    }
}
