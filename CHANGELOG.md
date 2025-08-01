# Changelog

All notable changes to the 84EM Local Pages Generator plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.1] - 2025-01-01

### Fixed
- GitHub Actions workflow simplified to basic syntax checks
- Resolved '84em is not a registered wp command' error in CI
- CI environment issues with WP-CLI command registration

### Changed
- GitHub Actions now only performs PHP syntax and composer.json validation
- Full test suite must be run locally due to CI limitations
- Updated TESTING.md to document CI restrictions

## [2.2.0] - 2025-01-01

### Added
- Comprehensive WP-CLI-based testing framework
- Custom TestCase class for WP-CLI testing without external dependencies
- Test command as subcommand: `wp 84em local-pages --test`
- 30 unit tests across 5 test suites:
  - encryption: API key encryption/decryption tests
  - data-structures: Service keywords and US states validation
  - content-processing: Content processing and title case tests
  - simple: Basic functionality tests
  - basic: WordPress environment tests
- TESTING.md documentation for testing procedures
- Composer configuration for WP-CLI testing dependencies

### Changed
- Test command structure to be subcommand of local-pages to avoid conflicts
- Updated README.md with testing section

### Fixed
- Fatal errors when running tests in WordPress environment
- Test compatibility with actual WordPress site URLs instead of example.com

## [2.1.1] - 2024-12-30

### Fixed
- PHP Fatal Error with associative array operations
- Array operations updated to use numeric indices with range()

## [2.1.0] - 2024-12-30

### Changed
- Service keywords from simple array to associative array with URL mappings
- Content generation prompt to include "30 years experience"
- Title case function to handle "84EM" as uppercase

## [2.0.1] - 2024-12-30

### Added
- H2/H3 heading processing to ensure no hyperlinks
- Title case conversion for all headings
- Special handling for "84EM" in title case conversion

### Changed
- All headings in generated content now use proper title case
- Headings are stripped of any links before processing

## [2.0.0] - 2024-12-15

### Added
- City-level page generation with hierarchical structure
- XML sitemap generation for all local pages
- Index page with alphabetized state listings
- Delete operations for states and cities
- Batch processing with progress tracking

### Changed
- Major refactoring of content generation
- Improved error handling and recovery
- Enhanced CLI command structure

## [1.0.0] - 2024-12-01

### Added
- Initial release
- State-level page generation for all 50 US states
- Claude AI integration for content generation
- WordPress custom post type for local pages
- SEO-optimized content with LD-JSON schema
- API key encryption using WordPress salts
- WP-CLI commands for page generation