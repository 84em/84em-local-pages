<?php
/**
 * Main Plugin Class
 *
 * @package EightyFourEM\LocalPages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages;

use EightyFourEM\LocalPages\Core\Requirements;
use EightyFourEM\LocalPages\Core\Activator;
use EightyFourEM\LocalPages\Core\Deactivator;
use EightyFourEM\LocalPages\PostTypes\LocalPostType;
use EightyFourEM\LocalPages\Api\Encryption;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Api\HealthCheckEndpoint;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
use EightyFourEM\LocalPages\Data\ServicesProvider;
use EightyFourEM\LocalPages\Content\StateContentGenerator;
use EightyFourEM\LocalPages\Content\CityContentGenerator;
use EightyFourEM\LocalPages\Schema\SchemaGenerator;
use EightyFourEM\LocalPages\Utils\ContentProcessor;
use EightyFourEM\LocalPages\Cli\CommandHandler;
use EightyFourEM\LocalPages\Cli\Commands\TestCommand;
use EightyFourEM\LocalPages\Cli\Commands\GenerateCommand;

/**
 * Main plugin bootstrap class
 */
class Plugin {
    /**
     * Plugin instance
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    /**
     * Dependency injection container
     *
     * @var Container
     */
    private Container $container;

    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function getInstance(): Plugin {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->container = new Container();
        $this->registerServices();
    }

    /**
     * Run the plugin
     */
    public function run(): void {
        // Check requirements
        $requirements = $this->container->get( Requirements::class );
        if ( ! $requirements->check() ) {
            return;
        }

        // Initialize plugin components
        $this->initializeComponents();
    }

    /**
     * Initialize plugin components
     */
    private function initializeComponents(): void {
        // Register custom post type
        $postType = $this->container->get( LocalPostType::class );
        $postType->register();

        // Register health check endpoint
        $healthCheck = $this->container->get( HealthCheckEndpoint::class );
        $healthCheck->register();

        // Register WP-CLI commands if available
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            $commandHandler = $this->container->get( CommandHandler::class );
            \WP_CLI::add_command( '84em local-pages', [ $commandHandler, 'handle' ] );
        }
    }

    /**
     * Register services in the container
     */
    private function registerServices(): void {
        // Core services
        $this->container->register( Requirements::class, function () {
            return new Requirements();
        } );

        $this->container->register( Activator::class, function () {
            return new Activator();
        } );

        $this->container->register( Deactivator::class, function () {
            return new Deactivator();
        } );

        // Post Types
        $this->container->register( LocalPostType::class, function () {
            return new LocalPostType();
        } );

        // Data Providers
        $this->container->register( StatesProvider::class, function () {
            return new StatesProvider();
        } );

        $this->container->register( KeywordsProvider::class, function () {
            return new KeywordsProvider();
        } );

        // API Services
        $this->container->register( Encryption::class, function () {
            return new Encryption();
        } );

        $this->container->register( ApiKeyManager::class, function ( $container ) {
            return new ApiKeyManager(
                $container->get( Encryption::class )
            );
        } );

        $this->container->register( ClaudeApiClient::class, function ( $container ) {
            return new ClaudeApiClient(
                $container->get( ApiKeyManager::class )
            );
        } );

        $this->container->register( HealthCheckEndpoint::class, function () {
            return new HealthCheckEndpoint();
        } );

        // Content Services
        $this->container->register( ContentProcessor::class, function ( $container ) {
            return new ContentProcessor(
                $container->get( KeywordsProvider::class )
            );
        } );

        $this->container->register( StateContentGenerator::class, function ( $container ) {
            return new StateContentGenerator(
                $container->get( ApiKeyManager::class ),
                $container->get( ClaudeApiClient::class ),
                $container->get( StatesProvider::class ),
                $container->get( KeywordsProvider::class ),
                $container->get( SchemaGenerator::class ),
                $container->get( ContentProcessor::class )
            );
        } );

        $this->container->register( CityContentGenerator::class, function ( $container ) {
            return new CityContentGenerator(
                $container->get( ApiKeyManager::class ),
                $container->get( ClaudeApiClient::class ),
                $container->get( StatesProvider::class ),
                $container->get( KeywordsProvider::class ),
                $container->get( SchemaGenerator::class ),
                $container->get( ContentProcessor::class )
            );
        } );

        // Schema Services
        $this->container->register( SchemaGenerator::class, function ( $container ) {
            return new SchemaGenerator(
                $container->get( StatesProvider::class )
            );
        } );

        // CLI Commands
        $this->container->register( TestCommand::class, function () {
            return new TestCommand();
        } );

        $this->container->register( GenerateCommand::class, function ( $container ) {
            return new GenerateCommand(
                $container->get( ApiKeyManager::class ),
                $container->get( StatesProvider::class ),
                $container->get( KeywordsProvider::class ),
                $container->get( StateContentGenerator::class ),
                $container->get( CityContentGenerator::class ),
                $container->get( ContentProcessor::class ),
                $container->get( SchemaGenerator::class )
            );
        } );

        $this->container->register( CommandHandler::class, function ( $container ) {
            return new CommandHandler(
                $container->get( ApiKeyManager::class ),
                $container->get( StatesProvider::class ),
                $container->get( KeywordsProvider::class ),
                $container->get( TestCommand::class ),
                $container->get( GenerateCommand::class )
            );
        } );

        // Register alias for backward compatibility and health check
        $this->container->register( 'api.key_manager', function ( $container ) {
            return $container->get( ApiKeyManager::class );
        } );
    }

    /**
     * Get the container
     *
     * @return Container
     */
    public function getContainer(): Container {
        return $this->container;
    }

    /**
     * Handle activation
     */
    public static function activate(): void {
        $instance  = self::getInstance();
        $activator = $instance->getContainer()->get( Activator::class );
        $activator->activate();

        // Register post type and flush rewrite rules
        $postType = $instance->getContainer()->get( LocalPostType::class );
        $postType->registerPostType();
        flush_rewrite_rules();
    }

    /**
     * Handle deactivation
     */
    public static function deactivate(): void {
        $instance    = self::getInstance();
        $deactivator = $instance->getContainer()->get( Deactivator::class );
        $deactivator->deactivate();
        flush_rewrite_rules();
    }
}
