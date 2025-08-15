<?php
/**
 * Main WP-CLI Command Handler
 *
 * @package EightyFourEM\LocalPages\Cli
 */

namespace EightyFourEM\LocalPages\Cli;

use EightyFourEM\LocalPages\Cli\Commands\TestCommand;
use EightyFourEM\LocalPages\Cli\Commands\GenerateCommand;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
use WP_CLI;

/**
 * Main CLI command handler that routes to appropriate command classes
 */
class CommandHandler {

    /**
     * API key manager instance
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
     * Test command handler
     *
     * @var TestCommand
     */
    private TestCommand $testCommand;

    /**
     * Generate command handler
     *
     * @var GenerateCommand
     */
    private GenerateCommand $generateCommand;

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

        // Initialize command handlers
        $this->testCommand     = new TestCommand();
        $this->generateCommand = new GenerateCommand(
            $apiKeyManager,
            $statesProvider,
            $keywordsProvider
        );
    }

    /**
     * Main WP-CLI command handler for all plugin commands
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments (flags)
     *
     * @return void
     */
    public function handle( array $args, array $assoc_args ): void {
        try {
            // Handle API key configuration
            if ( isset( $assoc_args['set-api-key'] ) ) {
                $this->handleApiKeySet();
                return;
            }

            // Handle API key validation
            if ( isset( $assoc_args['validate-api-key'] ) ) {
                $this->handleApiKeyValidation();
                return;
            }

            // Handle test command (doesn't require API key)
            if ( isset( $assoc_args['test'] ) ) {
                $this->testCommand->handle( $args, $assoc_args );
                return;
            }

            // Handle commands that don't require API key
            if ( isset( $assoc_args['generate-sitemap'] ) ) {
                $this->generateCommand->handleSitemapGeneration( $args, $assoc_args );
                return;
            }

            if ( isset( $assoc_args['generate-index'] ) ) {
                $this->generateCommand->handleIndexGeneration( $args, $assoc_args );
                return;
            }

            if ( isset( $assoc_args['regenerate-schema'] ) ) {
                $this->generateCommand->handleSchemaRegeneration( $args, $assoc_args );
                return;
            }

            // Handle delete operations (don't require API key)
            if ( isset( $assoc_args['delete'] ) ) {
                $this->generateCommand->handleDelete( $args, $assoc_args );
                return;
            }

            // If no specific command is provided, show help (doesn't require API key)
            if ( empty( $assoc_args )
                 || ( count( $assoc_args ) === 1 && isset( $assoc_args['help'] ) ) ) {
                $this->showHelp();
                return;
            }

            // Handle generation commands (require API key)
            if ( isset( $assoc_args['generate-all'] )
                 || isset( $assoc_args['update-all'] )
                 || isset( $assoc_args['state'] )
                 || isset( $assoc_args['city'] )
                 || isset( $assoc_args['update'] ) ) {

                // These commands require API key
                if ( ! $this->validateApiKey() ) {
                    WP_CLI::error( 'Claude API key not found or invalid. Please set it first using --set-api-key' );
                    return;
                }

                if ( isset( $assoc_args['generate-all'] ) ) {
                    $this->generateCommand->handleGenerateAll( $args, $assoc_args );
                    return;
                }

                if ( isset( $assoc_args['update-all'] ) ) {
                    $this->generateCommand->handleUpdateAll( $args, $assoc_args );
                    return;
                }

                // If both state and city are provided, handle as city command
                if ( isset( $assoc_args['state'] ) && isset( $assoc_args['city'] ) ) {
                    $this->generateCommand->handleCity( $args, $assoc_args );
                    return;
                }

                // Handle state-only command
                if ( isset( $assoc_args['state'] ) ) {
                    $this->generateCommand->handleState( $args, $assoc_args );
                    return;
                }

                // Handle city-only command (will error if no state provided)
                if ( isset( $assoc_args['city'] ) ) {
                    $this->generateCommand->handleCity( $args, $assoc_args );
                    return;
                }

                if ( isset( $assoc_args['update'] ) ) {
                    $this->generateCommand->handleUpdate( $args, $assoc_args );
                    return;
                }
            }

            // Default: show help for unrecognized commands
            $this->showHelp();

        } catch ( \Exception $e ) {
            WP_CLI::error( 'Command failed: ' . $e->getMessage() );
        }
    }

    /**
     * Handle API key setting
     *
     * @return void
     */
    private function handleApiKeySet(): void {
        WP_CLI::line( 'Setting Claude API Key' );
        WP_CLI::line( '=====================' );
        WP_CLI::line( '' );
        WP_CLI::line( 'For security reasons, please paste your Claude API key when prompted.' );
        WP_CLI::line( 'The key will not be visible as you type and will not appear in your shell history.' );
        WP_CLI::line( '' );

        // Disable echo for secure input
        if ( function_exists( 'system' ) ) {
            system( 'stty -echo' );
        }

        WP_CLI::out( 'Paste your Claude API key: ' );
        $handle  = fopen( 'php://stdin', 'r' );
        $api_key = trim( fgets( $handle ) );
        fclose( $handle );

        // Re-enable echo
        if ( function_exists( 'system' ) ) {
            system( 'stty echo' );
        }

        WP_CLI::line( '' ); // New line after hidden input

        // Debug: Show length of captured input
        WP_CLI::debug( 'Captured input length: ' . strlen( $api_key ) );

        if ( empty( $api_key ) ) {
            WP_CLI::error( 'No API key provided. Operation cancelled.' );
            return;
        }

        // Validate API key format (Claude keys start with 'sk-ant-')
        if ( ! str_starts_with( $api_key, 'sk-ant-' ) ) {
            WP_CLI::warning( 'API key format may be invalid. Claude API keys typically start with "sk-ant-".' );
            if ( ! WP_CLI::confirm( 'Continue anyway?' ) ) {
                WP_CLI::line( 'Operation cancelled.' );
                return;
            }
        }

        try {
            // Store the key first
            $result = $this->apiKeyManager->setKey( $api_key );
            if ( ! $result ) {
                WP_CLI::error( 'Failed to store the API key.' );
                return;
            }

            WP_CLI::success( 'Claude API key securely encrypted and stored.' );

            // Verify it was actually stored
            $verify = $this->apiKeyManager->getKey();
            if ( ! $verify ) {
                WP_CLI::error( 'Error: Key was stored but could not be retrieved for verification.' );
                return;
            }

            // Now try to validate with Claude API
            WP_CLI::line( '' );
            WP_CLI::line( 'Testing API key with Claude...' );

            $apiClient = new ClaudeApiClient( $this->apiKeyManager );
            if ( $apiClient->validateCredentials() ) {
                WP_CLI::success( 'API key is valid and working!' );
            }
            else {
                WP_CLI::warning( 'Could not validate API key with Claude. The key has been stored but may not be valid.' );
                WP_CLI::line( 'You can test it again later with: wp 84em local-pages --validate-api-key' );
            }

        } catch ( \Exception $e ) {
            WP_CLI::error( 'Failed to set API key: ' . $e->getMessage() );
        }
    }

    /**
     * Handle API key validation
     *
     * @return void
     */
    private function handleApiKeyValidation(): void {
        WP_CLI::line( 'Validating Stored API Key' );
        WP_CLI::line( '========================' );
        WP_CLI::line( '' );

        try {
            if ( ! $this->apiKeyManager->hasKey() ) {
                WP_CLI::error( 'No API key found. Please set one first using --set-api-key' );
                return;
            }

            WP_CLI::log( 'Found stored API key. Testing...' );

            $apiClient = new ClaudeApiClient( $this->apiKeyManager );
            if ( $apiClient->validateCredentials() ) {
                WP_CLI::success( 'Stored API key is valid and working!' );
            }
            else {
                WP_CLI::error( 'Stored API key is invalid or not working.' );
            }

        } catch ( \Exception $e ) {
            WP_CLI::error( 'Failed to validate API key: ' . $e->getMessage() );
        }
    }

    /**
     * Validate API key exists and is configured
     *
     * @return bool
     */
    private function validateApiKey(): bool {
        try {
            $api_key = $this->apiKeyManager->getKey();
            return ! empty( $api_key );
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Show help information
     *
     * @return void
     */
    private function showHelp(): void {
        WP_CLI::line( '' );
        WP_CLI::line( '84EM Local Pages Generator' );
        WP_CLI::line( '==========================' );
        WP_CLI::line( '' );
        WP_CLI::line( 'USAGE:' );
        WP_CLI::line( '  wp 84em local-pages [command] [options]' );
        WP_CLI::line( '' );
        WP_CLI::line( 'API KEY MANAGEMENT:' );
        WP_CLI::line( '  --set-api-key              Set/update Claude API key (interactive prompt)' );
        WP_CLI::line( '  --validate-api-key         Validate stored Claude API key' );
        WP_CLI::line( '' );
        WP_CLI::line( 'TESTING:' );
        WP_CLI::line( '  --test --all               Run all test suites' );
        WP_CLI::line( '  --test --suite=<name>      Run specific test suite' );
        WP_CLI::line( '                             Available: encryption, data-structures, content-processing' );
        WP_CLI::line( '' );
        WP_CLI::line( 'CONTENT GENERATION:' );
        WP_CLI::line( '  --generate-all             Generate/update all 350 pages (50 states + 300 cities)' );
        WP_CLI::line( '  --generate-all --states-only  Generate/update 50 state pages only' );
        WP_CLI::line( '  --update-all               Update all existing pages' );
        WP_CLI::line( '  --state="State Name"       Generate/update specific state page' );
        WP_CLI::line( '  --state="State" --city="City"  Generate/update specific city page' );
        WP_CLI::line( '  --state="State" --city=all     Generate/update all cities for a state' );
        WP_CLI::line( '  --state="State" --city=all --complete  Generate all cities AND update state page' );
        WP_CLI::line( '' );
        WP_CLI::line( 'MAINTENANCE:' );
        WP_CLI::line( '  --delete --state="State"   Delete state and all its cities' );
        WP_CLI::line( '  --delete --state="State" --city="City"  Delete specific city' );
        WP_CLI::line( '  --generate-sitemap         Generate XML sitemap for all local pages' );
        WP_CLI::line( '  --generate-index           Generate index page with all locations' );
        WP_CLI::line( '  --regenerate-schema        Regenerate schema markup for all pages' );
        WP_CLI::line( '' );
        WP_CLI::line( 'EXAMPLES:' );
        WP_CLI::line( '  wp 84em local-pages --set-api-key' );
        WP_CLI::line( '  wp 84em local-pages --test --all' );
        WP_CLI::line( '  wp 84em local-pages --generate-all --states-only' );
        WP_CLI::line( '  wp 84em local-pages --state="California"' );
        WP_CLI::line( '  wp 84em local-pages --state="California" --city="Los Angeles"' );
        WP_CLI::line( '  wp 84em local-pages --state="California" --city=all --complete' );
        WP_CLI::line( '' );
    }
}
