<?php
/**
 * Data Provider Interface
 *
 * @package EightyFourEM\LocalPages\Contracts
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Contracts;

/**
 * Interface for data provider services
 */
interface DataProviderInterface {
    /**
     * Get all data
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Get data by key
     *
     * @param  string  $key  Data key
     *
     * @return mixed|null
     */
    public function get( string $key ): mixed;

    /**
     * Check if data exists
     *
     * @param  string  $key  Data key
     *
     * @return bool
     */
    public function has( string $key ): bool;

    /**
     * Get data keys
     *
     * @return array
     */
    public function getKeys(): array;
}
