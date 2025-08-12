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
        // Flush rewrite rules on activation
        flush_rewrite_rules();

        // Set default options if they don't exist
        $this->setDefaultOptions();

        // Schedule cron events if needed
        $this->scheduleCronEvents();
    }

    /**
     * Set default plugin options
     */
    private function setDefaultOptions(): void {
        // Add default options if they don't exist
        if ( false === get_option( 'eightyfourem_local_pages_version' ) ) {
            add_option( 'eightyfourem_local_pages_version', EIGHTYFOUREM_LOCAL_PAGES_VERSION );
        }

        // Initialize progress tracking option
        if ( false === get_option( 'eightyfourem_generation_progress' ) ) {
            add_option( 'eightyfourem_generation_progress', [
                'current'    => 0,
                'total'      => 0,
                'status'     => 'idle',
                'last_state' => '',
                'errors'     => [],
            ] );
        }
    }

    /**
     * Schedule cron events
     */
    private function scheduleCronEvents(): void {
        // Add any scheduled tasks here if needed in the future
        // For example: wp_schedule_event( time(), 'daily', 'eightyfourem_daily_maintenance' );
    }
}
