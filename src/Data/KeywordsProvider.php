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
        $home_page     = site_url( '/' );
        $work_page     = site_url( '/work/' );
        $services_page = site_url( '/services/' );

        $this->data = [
            'WordPress development'                 => $work_page,
            'custom plugin development'             => $work_page,
            'API integrations'                      => $work_page,
            'security audits'                       => $work_page,
            'white-label development'               => $services_page,
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
            'WordPress plugin development'          => $services_page,
            'Custom WordPress plugin development'   => $home_page,
            'White label WordPress development'     => $services_page,
            'WordPress plugin development services' => $services_page,
        ];
    }
}
