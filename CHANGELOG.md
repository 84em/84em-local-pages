# Changelog

All notable changes to the 84EM Local Pages Generator plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.5] - 2025-08-15

### Fixed
- **Critical**: Fixed --generate-all command updating wrong post IDs for state pages
  - State page queries now check that `_local_page_city` does NOT exist
  - Prevents city pages from being mistakenly updated as state pages
- **City Page Titles**: Fixed city pages not having their titles updated during updates
  - Added `post_title` to the update array in CityContentGenerator::updateCityPage()
  - City pages now properly show "WordPress Development Services in {City}, {State} | 84EM"
- **Schema Generation**: Fixed missing schema generation in StateContentGenerator
  - State pages now generate schema on creation (generateStatePage)
  - State pages now regenerate schema on update (updateStatePage)
  - Ensures consistency with city pages which were already generating schema

### Changed
- Standardized schema meta key to use 'schema' instead of '_local_page_schema'
- SEO meta fields changed from Yoast (_yoast_wpseo_*) to Genesis Framework (_genesis_*)
  - Changed _yoast_wpseo_metadesc to _genesis_description
  - Changed _yoast_wpseo_title to _genesis_title
  - Removed _yoast_wpseo_canonical field

## [3.0.4] - 2025-08-15

### Fixed
- **Critical**: Fixed PHP syntax errors in content generator prompt strings
  - Escaped quotes in grammar rule examples to prevent parse errors (exit code 255)
  - Fixed both StateContentGenerator.php and CityContentGenerator.php
  - All PHP files now pass syntax validation checks

## [3.0.3] - 2025-08-15

### Fixed
- **Content Generation**: State pages now properly mention ALL 6 cities instead of only 4
  - Updated prompt to explicitly require mentioning all cities in the list
  - Changed from "cities like" to "ALL of these cities" with explicit instruction
- **Grammar Issues**: Fixed grammatical errors in generated content
  - Added proper preposition usage (in, for) with location names
  - Prevented awkward constructions like "Hoover businesses seeking Hoover solutions"
  - Added explicit grammar rules to both state and city content prompts
  - Keywords now use proper format: "WordPress development in {city}" instead of "WordPress development {city}"

### Changed
- Improved content generation prompts for better grammatical accuracy
- Enhanced location keyword formatting for more natural reading

### Documentation
- Updated prompt templates to reflect new grammar rules and city mention requirements

## [3.0.2] - 2025-08-15

### Fixed
- **Critical**: Fixed GitHub Actions deployment workflow triggering on dev branch pushes
  - Changed trigger from `push` events to `pull_request` with `types: [closed]`
  - Added job condition to check if PR was actually merged (`github.event.pull_request.merged == true`)
  - Removed tag-based deployment triggers that were causing unintended deployments
  - Deployment now ONLY occurs when PRs are merged to main branch or via manual dispatch from main

### Security
- Improved deployment security by preventing accidental production deployments from non-main branches
- Ensured deployment workflow cannot be triggered by pushing tags on any branch

## [3.0.1] - 2025-08-15

### Added
- New `--complete` flag for `--city=all` command to generate all cities AND update state page in one operation
- Comprehensive tests for block structure handling to prevent regression
- Tests for keyword linking with proper case preservation

### Fixed
- **Critical**: Fixed invalid WordPress block structure that prevented editing in Block Editor
  - ContentProcessor now detects existing block markup to prevent double-wrapping
  - Eliminated nested paragraph blocks and malformed HTML structures
- Fixed state page query bug where state commands incorrectly updated city pages
  - Added proper meta_query checking for `_local_page_city` NOT EXISTS
- Fixed city name interlinking in state pages
  - City names now properly link to their respective city pages
  - Processing order changed to handle location links before service keywords
  - Removed hardcoded location-specific keywords that interfered with dynamic linking
- Fixed service keyword linking for multi-word keywords
  - Improved regex pattern for better word boundary detection
  - Keywords like "API integrations" and "security audits" now link correctly
  - Preserves original case from content when creating links
- Implemented missing `--regenerate-schema` command functionality
  - Command was introduced in v2.2.2 but never implemented
  - Now properly regenerates LD-JSON schema for all pages without touching content
  - Supports all documented filtering options (states-only, specific state, specific city)

### Changed
- KeywordsProvider now uses `/services/` URL instead of `/wordpress-development-services/`
- Updated test expectations to match new service URLs
- Improved ContentProcessor to handle existing block content intelligently

### Removed
- Removed unused `getCities` method from StatesProvider (using `get()` method instead)

## [3.0.0] - 2025-08-12

### Changed
- **BREAKING CHANGE**: Complete architectural overhaul from monolithic class to modular architecture
- Migrated from single 2,954-line class to 20+ focused classes following SOLID principles
- Implemented PSR-4 autoloading with proper PHP namespaces (`EightyFourEM\LocalPages\*`)
- Restructured codebase into logical modules: Api, Cli, Content, Data, Schema, Utils
- Introduced dependency injection container pattern
- All 30 tests rewritten to work with new architecture
- Removed legacy monolithic class entirely

### Added
- Modern PHP 8.2 features including typed properties and union types
- Contracts/interfaces for better abstraction and testability
- Container class for dependency injection
- Dedicated command classes for CLI operations
- Proper separation of concerns with single responsibility per class
- Comprehensive error handling and logging

### Fixed
- Claude API model updated to correct version (`claude-sonnet-4-20250514`)
- Max tokens setting corrected to 4000
- Constructor parameter dependencies properly resolved
- CLI command routing for state/city parameters
- API key management method compatibility

### Improved
- Code maintainability and readability
- Test isolation and reliability
- Memory efficiency with lazy loading
- Plugin initialization flow
- Overall code organization

## [2.4.2] - 2025-08-12

### Fixed
- Fixed heredoc syntax errors throughout deploy.yml workflow causing YAML validation failures
- Corrected Slack notification to use environment variable instead of invalid webhook_url parameter
- Fixed all notification configurations to use secrets instead of repository variables
- Added proper handling for optional health check URL with graceful skip if not configured
- Removed all unnecessary export statements from SSH commands

### Changed
- All sensitive configuration (webhooks, emails, URLs) now properly stored as GitHub secrets
- SSH commands now pass variables as positional parameters for better security
- Added continue-on-error for optional notification steps
- Health check now properly skips when URL not configured instead of failing
- SSH_PORT now has default fallback value of 22

### Improved
- Cleaner, more maintainable workflow with consistent variable handling
- Better error handling for optional features (notifications, health checks)
- All heredocs now use unique delimiter names to prevent conflicts

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