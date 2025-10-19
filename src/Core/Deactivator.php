<?php
/**
 * Plugin Deactivator
 *
 * @package EightyFourEM\LocalPages\Core
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
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
        // Currently no cleanup needed on deactivation
        // The flush_rewrite_rules() is called in Plugin::deactivate()
        
        // Note: We intentionally keep the version option
        // so we know the last installed version if reactivated
    }
}