# 84EM Local Pages Generator - WP-CLI Testing

## Overview

This plugin uses WP-CLI as its exclusive testing framework. All tests are executed through custom WP-CLI commands, providing a streamlined testing experience that integrates directly with WordPress.

## Requirements

- WordPress installation with WP-CLI installed
- PHP 8.2 or higher
- The 84EM Local Pages plugin activated

## Running Tests

### Install Dependencies

```bash
composer install
```

### Execute Tests

All tests are run through the `wp 84em local-pages --test` command:

```bash
# Run all tests
wp 84em local-pages --test --all

# Run specific test suite
wp 84em local-pages --test --suite=encryption
wp 84em local-pages --test --suite=data-structures
wp 84em local-pages --test --suite=url-generation
wp 84em local-pages --test --suite=ld-json
wp 84em local-pages --test --suite=cli-args
wp 84em local-pages --test --suite=content-processing
wp 84em local-pages --test --suite=simple
```

### Available Test Suites (v3.1.0)

1. **encryption** - Tests for API key encryption and decryption (7 tests)
2. **data-structures** - Tests for service keywords and US states data (5 tests)
3. **content-processing** - Tests for ContentProcessor class methods (13 tests)
4. **cli-args** - Tests for WP-CLI argument parsing (12 tests)
5. **ld-json** - Tests for LD-JSON schema generation (14 tests)
6. **container** - Tests for dependency injection container (12 tests)
7. **api-client** - Tests for Claude API client (9 tests)
8. **content-generators** - Tests for state and city content generators (12 tests)
9. **error-handling** - Tests for error handling and logging (12 tests)
10. **security** - Tests for security features (10 tests)

**Total: 106 tests across 10 test suites**

## Test Files

All test files are located in the `tests/unit/` directory:

- `test-encryption.php` - API key encryption/decryption tests
- `test-data-structures.php` - Data structure validation tests
- `test-content-processing.php` - ContentProcessor class tests (actual methods)
- `test-wp-cli-args.php` - CLI argument parsing tests
- `test-ld-json-schema.php` - Schema generation tests
- `test-container.php` - Dependency injection container tests
- `test-api-client.php` - Claude API client tests
- `test-content-generators.php` - Content generator integration tests
- `test-error-handling.php` - Error handling and recovery tests
- `test-security.php` - Security feature tests

### Version 3.1.0 Test Improvements

Major refactoring of test suites to focus on actual plugin functionality:
- **Rewrote Tests**: content-processing, error-handling, and security tests now test actual plugin methods
- **Removed Tests**: Eliminated tests for WordPress core functions and imaginary features
- **Improved Coverage**: All tests now validate real business logic used in production
- **Better Architecture**: Tests align with actual plugin modular architecture

## Test Framework

The plugin includes a custom `TestCase` class (`tests/TestCase.php`) that provides assertion methods similar to PHPUnit but designed specifically for WP-CLI execution. This allows tests to run without requiring PHPUnit or other external testing frameworks.

### Available Assertions

- `assertEquals($expected, $actual, $message = '')`
- `assertTrue($value, $message = '')`
- `assertFalse($value, $message = '')`
- `assertNull($value, $message = '')`
- `assertNotNull($value, $message = '')`
- `assertArrayHasKey($key, $array, $message = '')`
- `assertStringContainsString($needle, $haystack, $message = '')`
- `assertIsArray($value, $message = '')`
- `assertCount($expected, $array, $message = '')`
- And many more...

## Writing New Tests

To add new tests:

1. Create a new file in `tests/unit/` following the naming pattern `test-{feature}.php`
2. Include the TestCase base class:
   ```php
   require_once dirname( __DIR__ ) . '/TestCase.php';
   ```
3. Create a test class extending TestCase:
   ```php
   class Test_Feature extends TestCase {
       public function setUp(): void {
           // Set up test environment
       }
       
       public function test_something() {
           // Test implementation
           $this->assertTrue($result);
       }
   }
   ```
4. Add the test suite to the `$test_suites` array in the `wp_cli_test_handler` method
5. Run your tests with `wp 84em local-pages --test --suite=feature`

## Mock Helpers

The testing framework includes mock helpers for simulating API responses:

- `tests/fixtures/mock-api-responses.php` - Claude API response mocking

## Test Output

When running tests, you'll see:

- 📋 Test file being run
- ✅ Passed tests
- ❌ Failed tests with error messages
- 📊 Summary with total, passed, and failed counts

## Continuous Integration

**Note**: Due to complexities with WP-CLI command registration in CI environments, GitHub Actions currently only runs basic syntax checks. Full test suite should be run locally.

To run tests locally:
```bash
wp 84em local-pages --test --all
```

The GitHub Actions workflow performs:
- PHP syntax validation for all files
- composer.json validation

Full WP-CLI tests must be run in a proper WordPress environment where the plugin can register its commands correctly.

## Troubleshooting

### Tests Not Found

If you get a "Test directory not found" error, ensure:
1. The plugin is activated
2. You're running the command from your WordPress root directory
3. The tests directory exists at `wp-content/plugins/84em-local-pages/tests/`

### Class Not Found

If you get a "Test class not found" error:
1. Check that the test file follows the naming convention
2. Ensure the class name matches the file name pattern
3. Verify the TestCase.php file exists

### WordPress Functions Not Available

Some tests may require WordPress functions. These are available when running through WP-CLI as the WordPress environment is already loaded.

### Critical Errors

Some tests that instantiate the plugin class directly may cause critical errors due to:
- The plugin already being loaded in WordPress
- Conflicts with singleton patterns or global state
- WordPress hooks being registered multiple times

Currently working test suites (v3.1.0):
- All 10 test suites are passing with 106 total tests
- Tests focus on actual plugin functionality
- No longer testing WordPress core or mock implementations

**Total: 106 tests, all passing**

Note: Some tests had to be modified to:
- Skip operations that would cause fatal errors when instantiating the plugin class multiple times
- Use actual site URLs instead of example.com
- Match the actual behavior of methods (e.g., title case and link generation)

## Testing Schema Regeneration

The plugin includes commands to regenerate LD-JSON schemas without regenerating content. To test:

```bash
# First, create some test pages
wp 84em local-pages --state="California"
wp 84em local-pages --state="California" --city="Los Angeles"

# Then test schema regeneration
wp 84em local-pages --regenerate-schema --state="California"
wp 84em local-pages --regenerate-schema --state="California" --city="Los Angeles"

# Verify the schema was updated
wp post meta get <post_id> schema
```

This is useful for:
- Fixing schema validation errors without API calls
- Updating schema structure after plugin updates
- Testing schema generation independently from content generation

## Benefits of WP-CLI Testing

1. **No External Dependencies** - No need for PHPUnit, Codeception, or other frameworks
2. **WordPress Integration** - Tests run in the actual WordPress environment
3. **Simple Execution** - Single command interface for all testing needs
4. **Lightweight** - Minimal setup and configuration required
5. **Production-Ready** - Can test in the same environment as production