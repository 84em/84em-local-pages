<?php
/**
 * Plugin Requirements Checker
 *
 * @package EightyFourEM\LocalPages\Core
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Core;

/**
 * Checks if system meets plugin requirements
 */
class Requirements {
    /**
     * Minimum WordPress version
     */
    private const MIN_WP_VERSION = '6.8';

    /**
     * Minimum PHP version
     */
    private const MIN_PHP_VERSION = '8.2';

    /**
     * Allowed domains
     */
    private const ALLOWED_DOMAINS
        = [
            'https://84em.com',
            'https://84em.local',
            'https://staging.84em.com',
        ];

    /**
     * Check all requirements
     *
     * @return bool True if all requirements are met
     */
    public function check(): bool {
        return $this->checkWordPressVersion()
               && $this->checkPhpVersion()
               && $this->checkDomain();
    }

    /**
     * Check WordPress version requirement
     *
     * @return bool
     */
    public function checkWordPressVersion(): bool {
        global $wp_version;

        if ( version_compare( $wp_version, self::MIN_WP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'wordPressVersionNotice' ] );
            $this->deactivatePlugin();
            return false;
        }

        return true;
    }

    /**
     * Check PHP version requirement
     *
     * @return bool
     */
    public function checkPhpVersion(): bool {
        if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'phpVersionNotice' ] );
            $this->deactivatePlugin();
            return false;
        }

        return true;
    }

    /**
     * Check domain requirement
     *
     * @return bool
     */
    public function checkDomain(): bool {
        $site_url = get_site_url();

        if ( ! in_array( $site_url, self::ALLOWED_DOMAINS, true ) ) {
            add_action( 'admin_notices', [ $this, 'domainNotice' ] );
            $this->deactivatePlugin();
            return false;
        }

        return true;
    }

    /**
     * Display WordPress version notice
     */
    public function wordPressVersionNotice(): void {
        $version = get_bloginfo( 'version' );
        echo '<div class="notice notice-error"><p><strong>84EM Local Pages Generator:</strong> ' .
             'This plugin requires WordPress ' . self::MIN_WP_VERSION . ' or higher. ' .
             'You are running version ' . esc_html( $version ) . '. ' .
             'The plugin has been deactivated.</p></div>';
    }

    /**
     * Display PHP version notice
     */
    public function phpVersionNotice(): void {
        echo '<div class="notice notice-error"><p><strong>84EM Local Pages Generator:</strong> ' .
             'This plugin requires PHP ' . self::MIN_PHP_VERSION . ' or higher. ' .
             'You are running version ' . PHP_VERSION . '. ' .
             'The plugin has been deactivated.</p></div>';
    }

    /**
     * Display domain notice
     */
    public function domainNotice(): void {
        echo '<div class="notice notice-error"><p><strong>84EM Local Pages Generator:</strong> ' .
             'This plugin can only be used on 84em.com. The plugin has been deactivated. ' .
             'Want a version that you can run on your own site? ' .
             '<a href="https://84em.com/contact/" target="_blank">Contact 84EM</a>.</p></div>';
    }

    /**
     * Deactivate the plugin
     */
    private function deactivatePlugin(): void {
        deactivate_plugins( '84em-local-pages/84em-local-pages.php' );
    }
}
