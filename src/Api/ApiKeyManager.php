<?php
/**
 * API Key Manager
 *
 * @package EightyFourEM\LocalPages\Api
 */

namespace EightyFourEM\LocalPages\Api;

/**
 * Manages API keys for external services
 */
class ApiKeyManager {
    /**
     * Option name for storing the encrypted API key
     */
    private const OPTION_NAME = '84em_claude_api_key_encrypted';

    /**
     * Encryption service
     *
     * @var Encryption
     */
    private Encryption $encryption;

    /**
     * Constructor
     *
     * @param  Encryption  $encryption  Encryption service
     */
    public function __construct( Encryption $encryption ) {
        $this->encryption = $encryption;
    }

    /**
     * Get the API key
     *
     * @return string|false Decrypted API key or false if not set
     */
    public function getKey(): string|false {
        $encrypted = get_option( self::OPTION_NAME );

        if ( empty( $encrypted ) ) {
            return false;
        }

        return $this->encryption->decrypt( $encrypted );
    }

    /**
     * Set the API key
     *
     * @param  string  $key  API key to store
     *
     * @return bool True on success, false on failure
     */
    public function setKey( string $key ): bool {
        if ( empty( $key ) ) {
            return false;
        }

        $encrypted = $this->encryption->encrypt( $key );

        if ( false === $encrypted ) {
            return false;
        }

        $result = update_option( self::OPTION_NAME, $encrypted );

        // Store a dummy IV for legacy compatibility (encryption includes IV in the data)
        update_option( '84em_claude_api_key_iv', base64_encode( random_bytes( 16 ) ) );

        return $result;
    }

    /**
     * Delete the API key
     *
     * @return bool True on success, false on failure
     */
    public function deleteKey(): bool {
        $result = delete_option( self::OPTION_NAME );
        delete_option( '84em_claude_api_key_iv' );
        return $result;
    }

    /**
     * Check if an API key is stored
     *
     * @return bool
     */
    public function hasKey(): bool {
        return false !== $this->getKey();
    }

    /**
     * Validate the stored API key format
     *
     * @return bool
     */
    public function validateStoredKey(): bool {
        $key = $this->getKey();

        if ( false === $key ) {
            return false;
        }

        return $this->validateKeyFormat( $key );
    }

    /**
     * Validate API key format
     *
     * @param  string  $key  API key to validate
     *
     * @return bool
     */
    public function validateKeyFormat( string $key ): bool {
        // Claude API keys start with 'sk-ant-api03-' followed by 93 characters
        return (bool) preg_match( '/^sk-ant-api03-[\w\-]{93}$/', $key );
    }
}
