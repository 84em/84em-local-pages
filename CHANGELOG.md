# Changelog

All notable changes to the 84EM Local Pages Generator plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.4.1] - 2025-08-12

### Changed
- Replaced custom security review implementation with official Anthropic Claude Code Security Review action
- Simplified security workflow from 600+ lines to ~75 lines
- Removed unnecessary configuration files and setup scripts
- Updated documentation to reflect simpler setup process

### Fixed
- Fixed PHP syntax error in test-url-generation.php (invalid array syntax)
- Fixed deployment workflow to properly check validation job success before proceeding
- Updated PHP syntax check to include test files (previously excluded)
- Added explicit validation result checks to all dependent jobs in deploy workflow
- Ensured deployment stops immediately if any PHP file has syntax errors

### Improved
- Better error handling in deployment workflow
- Job dependencies now properly enforce validation success
- Cleaner, more maintainable security review implementation

## [2.4.0] - 2025-08-12

### Added
- Automated security reviews using Claude AI for all pull requests
- Security review GitHub Actions workflow (`security-review.yml`)
- Configurable security review settings (`security-review-config.yml`)
- Support for multiple Claude models (Sonnet 4, Opus 4.1, etc.)
- Automated vulnerability detection for SQL injection, XSS, command injection, and more
- PR comment integration with detailed security findings
- Dependency security checks with composer and npm audit
- Static code analysis integration
- Setup script for easy API key configuration
- Security report artifacts saved for 30 days

### Changed
- Updated GitHub Actions workflows to use secure heredoc patterns for SSH commands
- Improved file handling with null-terminated input in PHP syntax checks
- Added file locking to prevent race conditions in backup cleanup
- Health check failures now trigger automatic rollback
- Replaced hardcoded values with environment variables throughout workflows
- Job dependencies now use explicit result checks instead of success()

### Fixed
- Fixed redundant PR check logic in deployment workflow
- Corrected PHP syntax check to properly detect and report errors
- Added proper escaping for SSH commands with secrets
- Fixed confusing job dependency conditions
- Secured legacy deploy.sh script by ensuring it's gitignored

### Security
- All SSH commands now use single-quoted heredocs to prevent variable expansion
- Environment variables properly exported before SSH execution
- Added flock mechanism for concurrent backup operations
- Removed SSH port fallback to prevent information disclosure

## [2.3.2] - 2025-08-12

### Security
- Added explicit blocking of deployments from pull requests in GitHub Actions workflow
- Enhanced deployment safety checks to prevent premature production deployments
- Added PR context verification even for main branch pushes
- Added debug output for blocked deployment attempts

### Fixed
- Deployment workflow now properly blocks all PR-related events
- Added multiple layers of safety checks to prevent accidental deployments before merge

### Changed
- Deployment decision logic now explicitly checks for pull_request events
- Added clearer error messages when deployment is blocked

## [2.3.1] - 2025-08-12

### Fixed
- LD-JSON schema URLs now correctly use actual page permalinks instead of hardcoded URL structure
- Schema generation functions now accept optional post_id parameter to retrieve real permalinks
- City page lookup improved to use meta_query for more reliable results
- Schema URLs now properly match the page URLs for both state and city pages

### Changed
- Updated `generate_ld_json_schema()` and `generate_city_ld_json_schema()` functions to use `get_permalink()` when post_id is available
- All schema generation calls now pass post_id parameter where available
- Improved city page search logic using meta_query instead of title search

## [2.3.0] - 2025-08-07

### Added
- GitHub Actions deployment workflow with comprehensive security features
- All sensitive deployment data (host, port, paths) stored as GitHub secrets
- Enhanced security scanning for dangerous PHP functions and credential patterns
- Automatic backup and rollback capabilities on deployment failure
- Optional deployment parameters (skip_backup, force_deploy, environment selection)
- Deployment hash verification for integrity checking
- Health check endpoint testing after deployment
- Multiple notification channels (Slack and email)
- Deployment info file with commit hash and metadata
- Support for custom SSH ports via secrets
- Configurable backup retention (keeps last 10 backups)

### Changed
- Migrated from rsync bash script (deploy.sh) to GitHub Actions workflow
- Deployment paths now stored as secrets instead of hardcoded values
- Enhanced pre-deployment validation with more comprehensive checks
- Improved error handling and deployment status reporting

### Security
- SSH private key stored encrypted in GitHub secrets
- All server credentials and paths moved to secure storage
- Enhanced security scanning for eval(), exec(), shell_exec() and other dangerous functions
- Credential pattern detection in code before deployment
- File permission validation to prevent world-writable PHP files

### Removed
- deploy.sh bash script (replaced by GitHub Actions)
- Hardcoded deployment paths from workflow files

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