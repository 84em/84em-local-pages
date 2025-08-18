<?php
/**
 * Service Keywords Data Provider
 *
 * @package EightyFourEM\LocalPages\Data
 */

namespace EightyFourEM\LocalPages\Data;

use EightyFourEM\LocalPages\Contracts\DataProviderInterface;

/**
 * Provides service keywords data
 */
class KeywordsProvider implements DataProviderInterface {
    /**
     * Keywords data cache
     *
     * @var array|null
     */
    private ?array $data = null;

    /**
     * Get all keywords data
     *
     * @return array
     */
    public function getAll(): array {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return $this->data;
    }

    /**
     * Get URL for a specific keyword
     *
     * @param  string  $key  Keyword
     *
     * @return mixed|null
     */
    public function get( string $key ): mixed {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return $this->data[ $key ] ?? null;
    }

    /**
     * Check if keyword exists
     *
     * @param  string  $key  Keyword
     *
     * @return bool
     */
    public function has( string $key ): bool {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return isset( $this->data[ $key ] );
    }

    /**
     * Get all keywords
     *
     * @return array
     */
    public function getKeys(): array {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return array_keys( $this->data );
    }

    /**
     * Load keywords data
     */
    private function loadData(): void {
        $work_page     = site_url( '/work/' );
        $services_page = site_url( '/services/' );
        $custom_plugin_development_page = site_url( '/services/custom-wordpress-plugin-development/' );
        $white_label_development_page = site_url( '/services/white-label-wordpress-development-for-agencies/' );

        $this->data = [
            'WordPress development'                 => $work_page,
            'custom plugin development'             => $custom_plugin_development_page,
            'API integrations'                      => $work_page,
            'security audits'                       => $work_page,
            'white-label development'               => $white_label_development_page,
            'WordPress maintenance'                 => $services_page,
            'WordPress support'                     => $services_page,
            'data migration'                        => $services_page,
            'platform transfers'                    => $services_page,
            'WordPress troubleshooting'             => $services_page,
            'custom WordPress themes'               => $services_page,
            'WordPress security'                    => $services_page,
            'web development'                       => $work_page,
            'WordPress migrations'                  => $services_page,
            'digital agency services'               => $services_page,
            'WordPress plugin development'          => $custom_plugin_development_page,
            'Custom WordPress plugin development'   => $custom_plugin_development_page,
            'White label WordPress development'     => $white_label_development_page,
            'White label web development'           => $white_label_development_page,
            'WordPress plugin development services' => $custom_plugin_development_page,
        ];
    }
}
