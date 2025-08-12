<?php
/**
 * API Client Interface
 *
 * @package EightyFourEM\LocalPages\Contracts
 */

namespace EightyFourEM\LocalPages\Contracts;

/**
 * Interface for external API clients
 */
interface ApiClientInterface {
    /**
     * Send a request to the API
     *
     * @param  string  $prompt  The prompt to send
     *
     * @return string|false Response content or false on failure
     */
    public function sendRequest( string $prompt ): string|false;

    /**
     * Check if the API client is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Validate API credentials
     *
     * @return bool
     */
    public function validateCredentials(): bool;
}
