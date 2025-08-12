<?php
/**
 * Plugin Deactivator
 *
 * @package EightyFourEM\LocalPages\Core
 */

namespace EightyFourEM\LocalPages\Core;

/**
 * Handles plugin deactivation
 */
class Deactivator {
    /**
     * Deactivate the plugin
     */
    public function deactivate(): void {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear scheduled cron events
        $this->clearCronEvents();

        // Clean up transients
        $this->cleanupTransients();
    }

    /**
     * Clear scheduled cron events
     */
    private function clearCronEvents(): void {
        // Clear any scheduled events
        // wp_clear_scheduled_hook( 'eightyfourem_daily_maintenance' );
    }

    /**
     * Clean up transients
     */
    private function cleanupTransients(): void {
        // Delete any plugin-specific transients
        delete_transient( 'eightyfourem_local_pages_cache' );
    }
}
