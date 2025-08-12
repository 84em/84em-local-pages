<?php
/**
 * Encryption Service
 *
 * @package EightyFourEM\LocalPages\Api
 */

namespace EightyFourEM\LocalPages\Api;

/**
 * Handles encryption and decryption of sensitive data
 */
class Encryption {
    /**
     * Encryption method
     */
    private const CIPHER_METHOD = 'AES-256-CBC';

    /**
     * Encrypt data
     *
     * @param  string  $data  Data to encrypt
     *
     * @return string|false Encrypted data or false on failure
     */
    public function encrypt( string $data ): string|false {
        if ( empty( $data ) ) {
            return false;
        }

        $key = $this->getEncryptionKey();
        $iv  = openssl_random_pseudo_bytes( openssl_cipher_iv_length( self::CIPHER_METHOD ) );

        $encrypted = openssl_encrypt(
            $data,
            self::CIPHER_METHOD,
            $key,
            0,
            $iv
        );

        if ( false === $encrypted ) {
            return false;
        }

        // Combine IV and encrypted data for storage
        return base64_encode( $iv . $encrypted );
    }

    /**
     * Decrypt data
     *
     * @param  string  $data  Encrypted data
     *
     * @return string|false Decrypted data or false on failure
     */
    public function decrypt( string $data ): string|false {
        if ( empty( $data ) ) {
            return false;
        }

        $key  = $this->getEncryptionKey();
        $data = base64_decode( $data );

        if ( false === $data ) {
            return false;
        }

        $iv_length = openssl_cipher_iv_length( self::CIPHER_METHOD );
        $iv        = substr( $data, 0, $iv_length );
        $encrypted = substr( $data, $iv_length );

        $decrypted = openssl_decrypt(
            $encrypted,
            self::CIPHER_METHOD,
            $key,
            0,
            $iv
        );

        return $decrypted ?: false;
    }

    /**
     * Get the encryption key
     *
     * @return string
     */
    private function getEncryptionKey(): string {
        // Use WordPress salts for encryption key
        $salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : '';
        $salt .= defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : '';

        // If no salts are defined, use a fallback
        if ( empty( $salt ) ) {
            $salt = 'eightyfourem-local-pages-default-salt-' . get_site_url();
        }

        // Return full 64-character hash for backward compatibility
        // OpenSSL will use the first 32 bytes for AES-256
        return hash( 'sha256', $salt );
    }

    /**
     * Validate that encryption is working properly
     *
     * @return bool
     */
    public function validate(): bool {
        $test_string = 'test_encryption_' . time();
        $encrypted   = $this->encrypt( $test_string );

        if ( false === $encrypted ) {
            return false;
        }

        $decrypted = $this->decrypt( $encrypted );

        return $decrypted === $test_string;
    }
}
