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
     * @param  TestCommand  $testCommand
     * @param  GenerateCommand  $generateCommand
     */
    public function __construct(
        ApiKeyManager $apiKeyManager,
        StatesProvider $statesProvider,
        KeywordsProvider $keywordsProvider,
        TestCommand $testCommand,
        GenerateCommand $generateCommand
    ) {
        $this->apiKeyManager    = $apiKeyManager;
        $this->statesProvider   = $statesProvider;
        $this->keywordsProvider = $keywordsProvider;
        $this->testCommand      = $testCommand;
        $this->generateCommand  = $generateCommand;
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
            // Validate arguments before processing
            $this->validateArguments( $args, $assoc_args );
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
     * Validate command arguments and provide helpful error messages
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments (flags)
     *
     * @return void
     * @throws \Exception
     */
    private function validateArguments( array $args, array $assoc_args ): void {
        // Check for positional arguments that look like they should be named arguments
        $this->checkForMissingDashes( $args );
        
        // Check for unrecognized named arguments
        $this->checkForUnrecognizedArguments( $assoc_args );
        
        // Check for mutually exclusive arguments
        $this->checkForMutuallyExclusiveArguments( $assoc_args );
        
        // Check for incomplete argument combinations
        $this->checkForIncompleteArguments( $assoc_args );
    }

    /**
     * Check for positional arguments that look like they should be named arguments
     *
     * @param  array  $args  Positional arguments
     *
     * @return void
     * @throws \Exception
     */
    private function checkForMissingDashes( array $args ): void {
        if ( empty( $args ) ) {
            return;
        }

        $problematic_args = [];
        $suggestions = [];

        foreach ( $args as $arg ) {
            // Check for key=value patterns without --
            if ( preg_match( '/^([a-zA-Z-]+)=(.*)$/', $arg, $matches ) ) {
                $key = $matches[1];
                $value = $matches[2];
                
                // Check if this looks like a valid argument name
                if ( $this->isValidArgumentName( $key ) ) {
                    $problematic_args[] = $arg;
                    $suggestions[] = "--{$key}=\"$value\"";
                }
            }
            // Check for standalone argument names that should have --
            elseif ( $this->isValidArgumentName( $arg ) ) {
                $problematic_args[] = $arg;
                $suggestions[] = "--{$arg}";
            }
            // Check for common typos
            elseif ( $this->isLikelyTypo( $arg ) ) {
                $problematic_args[] = $arg;
                $suggestion = $this->getSuggestionForTypo( $arg );
                if ( $suggestion ) {
                    $suggestions[] = $suggestion;
                }
            }
        }

        if ( ! empty( $problematic_args ) ) {
            $error_msg = "Invalid positional arguments detected:\n";
            
            foreach ( $problematic_args as $i => $arg ) {
                $error_msg .= "  \"$arg\"\n";
                if ( isset( $suggestions[$i] ) ) {
                    $error_msg .= "    Did you mean: {$suggestions[$i]}\n";
                }
            }
            
            $error_msg .= "\nRemember: All options must start with '--' (two dashes).\n";
            $error_msg .= "Examples:\n";
            $error_msg .= "  wp 84em local-pages --state=\"California\"\n";
            $error_msg .= "  wp 84em local-pages --state=\"California\" --city=\"Los Angeles\"\n";
            $error_msg .= "  wp 84em local-pages --generate-all --states-only\n";
            
            throw new \Exception( $error_msg );
        }
    }

    /**
     * Check if a string looks like a valid argument name
     *
     * @param  string  $name
     *
     * @return bool
     */
    private function isValidArgumentName( string $name ): bool {
        $valid_args = [
            'state', 'city', 'test', 'suite', 'generate-all', 'update-all',
            'states-only', 'complete', 'set-api-key', 'validate-api-key',
            'generate-sitemap', 'generate-index', 'regenerate-schema',
            'delete', 'update', 'help', 'all'
        ];
        
        return in_array( strtolower( $name ), $valid_args, true );
    }

    /**
     * Check if a string is likely a typo of a valid argument
     *
     * @param  string  $arg
     *
     * @return bool
     */
    private function isLikelyTypo( string $arg ): bool {
        $common_typos = [
            'State', 'STATE', 'City', 'CITY', 'Test', 'TEST',
            'generateall', 'generate_all', 'updateall', 'update_all',
            'statesonly', 'states_only', 'setapikey', 'set_api_key'
        ];
        
        return in_array( $arg, $common_typos, true );
    }

    /**
     * Get suggestion for a likely typo
     *
     * @param  string  $typo
     *
     * @return string|null
     */
    private function getSuggestionForTypo( string $typo ): ?string {
        $typo_map = [
            'State' => '--state',
            'STATE' => '--state',
            'City' => '--city',
            'CITY' => '--city',
            'Test' => '--test',
            'TEST' => '--test',
            'generateall' => '--generate-all',
            'generate_all' => '--generate-all',
            'updateall' => '--update-all',
            'update_all' => '--update-all',
            'statesonly' => '--states-only',
            'states_only' => '--states-only',
            'setapikey' => '--set-api-key',
            'set_api_key' => '--set-api-key'
        ];
        
        return $typo_map[$typo] ?? null;
    }

    /**
     * Check for unrecognized named arguments
     *
     * @param  array  $assoc_args
     *
     * @return void
     * @throws \Exception
     */
    private function checkForUnrecognizedArguments( array $assoc_args ): void {
        $valid_args = [
            'state', 'city', 'test', 'suite', 'generate-all', 'update-all',
            'states-only', 'complete', 'set-api-key', 'validate-api-key',
            'generate-sitemap', 'generate-index', 'regenerate-schema',
            'delete', 'update', 'help', 'all'
        ];
        
        $unrecognized = [];
        $suggestions = [];
        
        foreach ( array_keys( $assoc_args ) as $arg ) {
            if ( ! in_array( $arg, $valid_args, true ) ) {
                $unrecognized[] = $arg;
                
                // Try to find a close match
                $suggestion = $this->findClosestArgument( $arg, $valid_args );
                if ( $suggestion ) {
                    $suggestions[$arg] = $suggestion;
                }
            }
        }
        
        if ( ! empty( $unrecognized ) ) {
            $error_msg = "Unrecognized arguments:\n";
            
            foreach ( $unrecognized as $arg ) {
                $error_msg .= "  --$arg\n";
                if ( isset( $suggestions[$arg] ) ) {
                    $error_msg .= "    Did you mean: --{$suggestions[$arg]}?\n";
                }
            }
            
            $error_msg .= "\nUse 'wp 84em local-pages --help' to see all available options.\n";
            
            throw new \Exception( $error_msg );
        }
    }

    /**
     * Find the closest valid argument using Levenshtein distance
     *
     * @param  string  $input
     * @param  array  $valid_args
     *
     * @return string|null
     */
    private function findClosestArgument( string $input, array $valid_args ): ?string {
        $min_distance = PHP_INT_MAX;
        $closest = null;
        
        foreach ( $valid_args as $valid_arg ) {
            $distance = levenshtein( strtolower( $input ), strtolower( $valid_arg ) );
            
            // Only suggest if it's reasonably close (within 3 character changes)
            if ( $distance < $min_distance && $distance <= 3 ) {
                $min_distance = $distance;
                $closest = $valid_arg;
            }
        }
        
        return $closest;
    }

    /**
     * Check for mutually exclusive arguments
     *
     * @param  array  $assoc_args
     *
     * @return void
     * @throws \Exception
     */
    private function checkForMutuallyExclusiveArguments( array $assoc_args ): void {
        $exclusive_groups = [
            // Generation commands are mutually exclusive
            [
                'generate-all', 'update-all', 'state', 'city', 'update',
                'generate-sitemap', 'generate-index', 'regenerate-schema', 'delete'
            ],
            // API key commands are mutually exclusive with everything
            ['set-api-key', 'validate-api-key'],
            // Test command is exclusive with generation
            ['test']
        ];
        
        $conflicts = [];
        
        foreach ( $exclusive_groups as $group ) {
            $found_in_group = [];
            
            foreach ( $group as $arg ) {
                if ( isset( $assoc_args[$arg] ) ) {
                    $found_in_group[] = $arg;
                }
            }
            
            // Check if this group conflicts with other groups
            if ( count( $found_in_group ) > 0 ) {
                // Check against other groups
                foreach ( $exclusive_groups as $other_group ) {
                    if ( $group === $other_group ) {
                        // Within same group, only allow one unless it's the main generation group
                        if ( $group[0] !== 'generate-all' && count( $found_in_group ) > 1 ) {
                            $conflicts[] = $found_in_group;
                        }
                    } else {
                        $found_in_other = [];
                        foreach ( $other_group as $arg ) {
                            if ( isset( $assoc_args[$arg] ) ) {
                                $found_in_other[] = $arg;
                            }
                        }
                        
                        if ( ! empty( $found_in_other ) ) {
                            $conflicts[] = array_merge( $found_in_group, $found_in_other );
                        }
                    }
                }
            }
        }
        
        if ( ! empty( $conflicts ) ) {
            $unique_conflicts = array_unique( $conflicts, SORT_REGULAR );
            $error_msg = "Conflicting arguments detected:\n";
            
            foreach ( $unique_conflicts as $conflict_group ) {
                $error_msg .= "  Cannot use together: --" . implode( ', --', $conflict_group ) . "\n";
            }
            
            $error_msg .= "\nPlease use only one command at a time.\n";
            
            throw new \Exception( $error_msg );
        }
    }

    /**
     * Check for incomplete argument combinations
     *
     * @param  array  $assoc_args
     *
     * @return void
     * @throws \Exception
     */
    private function checkForIncompleteArguments( array $assoc_args ): void {
        // Check for test command without required parameters
        if ( isset( $assoc_args['test'] ) && ! isset( $assoc_args['all'] ) && ! isset( $assoc_args['suite'] ) ) {
            throw new \Exception(
                "Test command requires either --all or --suite=<name>\n" .
                "Examples:\n" .
                "  wp 84em local-pages --test --all\n" .
                "  wp 84em local-pages --test --suite=encryption\n"
            );
        }
        
        // Check for city without state
        if ( isset( $assoc_args['city'] ) && ! isset( $assoc_args['state'] ) ) {
            throw new \Exception(
                "City argument requires a state to be specified\n" .
                "Examples:\n" .
                "  wp 84em local-pages --state=\"California\" --city=\"Los Angeles\"\n" .
                "  wp 84em local-pages --state=\"California\" --city=all\n"
            );
        }
        
        // Check for delete without target
        if ( isset( $assoc_args['delete'] ) && ! isset( $assoc_args['state'] ) ) {
            throw new \Exception(
                "Delete command requires at least a state to be specified\n" .
                "Examples:\n" .
                "  wp 84em local-pages --delete --state=\"California\"\n" .
                "  wp 84em local-pages --delete --state=\"California\" --city=\"Los Angeles\"\n"
            );
        }
        
        // Check for suite without test
        if ( isset( $assoc_args['suite'] ) && ! isset( $assoc_args['test'] ) ) {
            throw new \Exception(
                "Suite argument can only be used with --test\n" .
                "Example:\n" .
                "  wp 84em local-pages --test --suite=encryption\n"
            );
        }
        
        // Check for states-only with inappropriate commands
        if ( isset( $assoc_args['states-only'] ) ) {
            $valid_with_states_only = ['generate-all', 'update-all', 'regenerate-schema'];
            $has_valid_command = false;
            
            foreach ( $valid_with_states_only as $valid_cmd ) {
                if ( isset( $assoc_args[$valid_cmd] ) ) {
                    $has_valid_command = true;
                    break;
                }
            }
            
            if ( ! $has_valid_command ) {
                throw new \Exception(
                    "--states-only can only be used with --generate-all, --update-all, or --regenerate-schema\n" .
                    "Examples:\n" .
                    "  wp 84em local-pages --generate-all --states-only\n" .
                    "  wp 84em local-pages --update-all --states-only\n"
                );
            }
        }
        
        // Check for complete without city=all
        if ( isset( $assoc_args['complete'] ) ) {
            if ( ! isset( $assoc_args['city'] ) || $assoc_args['city'] !== 'all' ) {
                throw new \Exception(
                    "--complete can only be used with --city=all\n" .
                    "Example:\n" .
                    "  wp 84em local-pages --state=\"California\" --city=all --complete\n"
                );
            }
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
