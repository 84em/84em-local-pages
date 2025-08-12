<?php
/**
 * Content Generator Interface
 *
 * @package EightyFourEM\LocalPages\Contracts
 */

namespace EightyFourEM\LocalPages\Contracts;

/**
 * Interface for content generation services
 */
interface ContentGeneratorInterface {
    /**
     * Generate content based on provided data
     *
     * @param  array  $data  Data for content generation
     *
     * @return string Generated content
     */
    public function generate( array $data ): string;

    /**
     * Validate that required data is present
     *
     * @param  array  $data  Data to validate
     *
     * @return bool
     */
    public function validate( array $data ): bool;
}
