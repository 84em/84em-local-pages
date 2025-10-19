<?php
/**
 * Schema Generator Interface
 *
 * @package EightyFourEM\LocalPages\Contracts
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Contracts;

/**
 * Interface for Schema.org structured data generation
 */
interface SchemaGeneratorInterface {
    /**
     * Generate Schema.org JSON-LD structured data
     *
     * @param  array  $data  Data for schema generation
     *
     * @return string JSON-LD schema
     */
    public function generate( array $data ): string;

    /**
     * Validate schema data
     *
     * @param  string  $schema  JSON-LD schema to validate
     *
     * @return bool
     */
    public function validate( string $schema ): bool;
}
