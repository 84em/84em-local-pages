<?php
/**
 * Plugin Activator
 *
 * @package EightyFourEM\LocalPages\Core
 */

namespace EightyFourEM\LocalPages\Core;

/**
 * Handles plugin activation
 */
class Activator {
    /**
     * Activate the plugin
     */
    public function activate(): void {
        // Store plugin version for potential future use
        if ( false === get_option( 'eightyfourem_local_pages_version' ) ) {
            add_option( 'eightyfourem_local_pages_version', EIGHTYFOUREM_LOCAL_PAGES_VERSION );
        }
        
        // Note: flush_rewrite_rules() is called in Plugin::activate()
        // after registering the post type
    }
}