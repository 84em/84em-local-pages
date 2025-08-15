<?php
/**
 * Plugin Name: 84EM Local Pages Generator
 * Description: Generates SEO-optimized Local Pages for each US state using Claude AI. Includes WP-CLI testing framework.
 * Version: 3.0.4
 * Author: 84EM
 * Requires at least: 6.8
 * Requires PHP: 8.2
 * Text Domain: 84em-local-pages
 */

defined( 'ABSPATH' ) or die;

// Define plugin constants
const EIGHTYFOUREM_LOCAL_PAGES_VERSION = '3.0.4';

// Load Composer autoloader
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Initialize and run the plugin using the new architecture
if ( class_exists( 'EightyFourEM\LocalPages\Plugin' ) ) {
    // Get plugin instance and run
    add_action( 'plugins_loaded', function () {
        $plugin = EightyFourEM\LocalPages\Plugin::getInstance();
        $plugin->run();
    } );

    // Register activation hook
    register_activation_hook( __FILE__, [ 'EightyFourEM\LocalPages\Plugin', 'activate' ] );

    // Register deactivation hook
    register_deactivation_hook( __FILE__, [ 'EightyFourEM\LocalPages\Plugin', 'deactivate' ] );
}
else {
    // Fatal error if autoloader failed
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>84EM Local Pages Generator Error:</strong> ' .
             'Failed to load plugin dependencies. Please run <code>composer install</code> in the plugin directory.</p></div>';
    } );
}
