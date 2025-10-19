<?php
/**
 * Test configuration for 84EM Local Pages plugin tests
 *
 * This class provides configuration management for tests.
 * Tests always use real API keys and make real API calls.
 *
 * @package EightyFourEM_Local_Pages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

class TestConfig {

	/**
	 * Get test API key
	 *
	 * Returns the production API key for use in tests.
	 * Tests store their data in test_ prefixed options, so using
	 * the production key for testing is safe - it never modifies production data.
	 *
	 * @return string Test API key (production key read directly from database)
	 */
	public static function getTestApiKey(): string {
		// Read the PRODUCTION option directly (not test_ prefixed)
		// This allows tests to use the real API key while storing test data separately
		$encrypted = get_option( '84em_local_pages_claude_api_key_encrypted' );
		if ( empty( $encrypted ) ) {
			// If no production key exists, tests cannot run
			// This should trigger clear error messages in tests
			return '';
		}

		// Decrypt the production key
		$encryption = new \EightyFourEM\LocalPages\Api\Encryption();
		$decrypted = $encryption->decrypt( $encrypted );

		return $decrypted ?: '';
	}

	/**
	 * Get test model identifier
	 *
	 * Returns the Claude model to use for testing.
	 * Can be overridden with environment variable or WordPress option.
	 *
	 * @return string Model identifier (defaults to production model or claude-sonnet-4-20250514)
	 */
	public static function getTestModel(): string {
		// Try environment variable first
		$model = getenv( 'EIGHTYFOUREM_TEST_MODEL' );
		if ( $model && ! empty( $model ) ) {
			return $model;
		}

		// Fall back to production model
		$production_model = get_option( '84em_local_pages_claude_api_model', false );
		if ( $production_model && ! empty( $production_model ) ) {
			return $production_model;
		}

		// Default to claude-sonnet-4-20250514
		return 'claude-sonnet-4-20250514';
	}

}
