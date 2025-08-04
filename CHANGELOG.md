# Changelog

All notable changes to the 84EM Local Pages Generator plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.3] - 2025-08-04

### Fixed
- Removed invalid `position` property from Offer type in LD-JSON schemas
- Position property is only valid for ListItem types (BreadcrumbList, ItemList), not Offer
- OfferCatalog schema now fully compliant with schema.org specifications

## [2.2.2] - 2025-08-04

### Added
- New `--regenerate-schema` WP-CLI command to fix schema issues without regenerating content
- Schema regeneration supports all pages, states-only, specific states, and specific cities
- Progress tracking for bulk schema regeneration operations
- Documentation for schema regeneration in README.md and TESTING.md

### Fixed
- Removed invalid `addressRegion` property from City type in LD-JSON schemas
- City types in schemas now properly use `containedInPlace` instead of `addressRegion`
- Cleaned up schema structure to use only valid schema.org properties
- LD-JSON validation errors reported by SEO tools like Ahrefs

### Changed
- Schema regeneration doesn't require Claude API key since it only updates metadata
- Updated plugin version to 2.2.2

## [2.2.1] - 2025-08-01

### Fixed
- GitHub Actions workflow simplified to basic syntax checks
- Resolved '84em is not a registered wp command' error in CI
- CI environment issues with WP-CLI command registration

### Changed
- GitHub Actions now only performs PHP syntax and composer.json validation
- Full test suite must be run locally due to CI limitations
- Updated TESTING.md to document CI restrictions

## [2.2.0] - 2025-08-01

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

## [2.1.1] - 2025-08-01

### Fixed
- PHP TypeError when generating LD-JSON schema due to associative service keywords array
- array_map position calculation now uses numeric indices instead of string keys
- Service keywords list generation now uses array_keys() to extract keyword names

## [2.1.0] - 2025-08-01

### Added
- Smart service keyword linking - keywords now link to contextually relevant pages
- Dynamic URL mapping for service keywords (work, services, projects, local pages)
- Special case handling for "84EM" as all caps in title case function

### Changed
- State page prompt updated with "30 years experience" and "diverse client industries"
- Service keywords structure from simple array to associative array with URL mappings
- Title case function now properly handles "84EM" as uppercase

## [2.0.1] - 2025-08-01

### Added
- `process_headings()` function to clean up heading formatting
- `convert_to_title_case()` function following standard title case rules
- Smart title case conversion (keeps articles/prepositions lowercase)

### Fixed
- H2 and H3 headings now properly formatted with title case
- Removed hyperlinks from within H2 and H3 headings

### Changed
- Content processing pipeline now includes heading cleanup step before interlinking
- Regex-based heading detection for WordPress block format
- Maintains WordPress block structure and `<strong>` tags in headings

## [2.0.0] - 2025-07-31

### Added
- Complete city page generation system (300 city pages, 6 per state)
- Hierarchical post type support with parent-child relationships (states → cities)
- Automatic interlinking system for city names and service keywords
- Clean hierarchical URL structure (`/wordpress-development-services-state/city/`)
- Separate Claude AI prompts for state pages (300-400 words) and city pages (250-350 words)
- Bulk operations: `--generate-all` and `--update-all` commands
- City-specific WP-CLI commands with `--city` parameter
- Comprehensive progress tracking with detailed statistics
- Real-time feedback during bulk operations
- Next steps guidance after command completion
- Enhanced error handling for hierarchical operations
- Validation for parent-child relationships
- Custom fields for city pages (`_local_page_city`)
- Hierarchical rewrite rules for clean city URLs
- Enhanced SEO with separate LD-JSON schemas for states and cities
- Progress bars for bulk operations with ETA tracking
- Hierarchical processing order (states first, then cities)
- Parent page validation for city creation
- Automatic link processing functions with collision avoidance

### Changed
- Total page capacity increased from 50 to 350 pages
- API cost estimates updated to reflect new scale ($14-28 for full generation)
- Post type now supports hierarchical structure (`'hierarchical' => true`)
- Index page generation now filters for state pages only
- Enhanced sitemap generation to include all city pages

## [1.0.0] - 2025-07-30

### Added
- WordPress plugin for generating SEO-optimized local pages
- Claude AI integration using Sonnet 4 model for content generation
- Custom "local" post type for WordPress development service pages
- WP-CLI integration with comprehensive command structure
- Support for all 50 US states with 6 largest cities per state
- State-specific landing pages (300-400 words each)
- WordPress Block Editor (Gutenberg) format support
- Automated CTA placement before H2 headings
- Clean URL structure without post type slug
- SEO optimization with titles, meta descriptions, and LD-JSON schema
- XML sitemap generation for all local pages
- Master index page with alphabetized state directory
- AES-256-CBC encryption for API key storage with WordPress salts
- Rate limiting with 1-second delays between API requests
- Progress tracking with real-time duration monitoring
- Comprehensive error handling and logging
- Professional, factual tone without industry specialization
- Geographic relevance through city mentions and remote-first messaging