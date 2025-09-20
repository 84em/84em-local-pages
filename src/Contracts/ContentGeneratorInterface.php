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

    /**
     * Retrieves the title of a post from the given data string.
     *
     * @param  string  $data  City or state
     *
     * @return string The title of the post.
     */
    public function getPostTitle( string $data ): string;

    /**
     * Retrieves the meta description from the provided data.
     *
     * @param  string  $data  The input data from which the meta description is extracted.
     * @param  string|null  $cities  Additional cities data for meta description generation.
     *
     * @return string The extracted meta description.
     */
    public function getMetaDescription( string $data, string $cities = null ): string;
}
