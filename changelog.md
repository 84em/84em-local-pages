# 84EM Local Pages Generator - Changelog

All notable changes to this plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.1] - August 1, 2025

### 🐛 Bug Fixes & Improvements

#### Content Processing Enhancement
- **Fixed**: H2 and H3 headings now properly formatted with title case
- **Fixed**: Removed hyperlinks from within H2 and H3 headings
- **Added**: `process_headings()` function to clean up heading formatting
- **Added**: `convert_to_title_case()` function following standard title case rules
- **Improved**: Heading processing applied before automatic interlinking to prevent conflicts

### 🛠️ Technical Details
- **Changed**: Content processing pipeline now includes heading cleanup step
- **Added**: Regex-based heading detection for WordPress block format
- **Added**: Smart title case conversion (keeps articles/prepositions lowercase)
- **Improved**: Maintains WordPress block structure and `<strong>` tags in headings

---

## [2.0.0] - July 31, 2025

### 🚀 Major New Features

#### Hierarchical City Pages System
- **Added**: Complete city page generation system (300 city pages, 6 per state)
- **Added**: Hierarchical post type support with parent-child relationships (states → cities)
- **Added**: Automatic interlinking system for city names and service keywords
- **Added**: Clean hierarchical URL structure (`/state/city/`)
- **Added**: Separate Claude AI prompts for state pages (300-400 words) and city pages (250-350 words)

#### New WP-CLI Commands
- **Added**: `--generate-all` command to create all 350 pages (50 states + 300 cities)
- **Added**: `--generate-all --states-only` to create only state pages
- **Added**: `--update-all` command to update all existing pages
- **Added**: `--update-all --states-only` to update only existing state pages
- **Added**: `--city` parameter for city-specific operations
- **Added**: City management commands: create, update, delete individual cities
- **Added**: Bulk city operations per state: `--state="California" --city=all`

#### Content Enhancement
- **Added**: Automatic interlinking of city names to their respective city pages
- **Added**: Automatic interlinking of service keywords to contact page (https://84em.com/contact/)
- **Added**: Enhanced SEO with separate LD-JSON schemas for states and cities
- **Added**: Custom fields for city pages (`_local_page_city`)
- **Added**: Hierarchical rewrite rules for clean city URLs

#### User Experience Improvements
- **Added**: Comprehensive progress tracking with detailed statistics
- **Added**: Real-time feedback during bulk operations
- **Added**: Next steps guidance after command completion
- **Added**: Enhanced error handling for hierarchical operations
- **Added**: Validation for parent-child relationships

### ⚡ Performance & Scale
- **Changed**: Total page capacity increased from 50 to 350 pages
- **Changed**: API cost estimates updated to reflect new scale ($14-28 for full generation)
- **Added**: Progress bars for bulk operations with ETA tracking
- **Added**: Hierarchical processing order (states first, then cities)

### 🛠️ Technical Improvements
- **Changed**: Post type now supports hierarchical structure (`'hierarchical' => true`)
- **Added**: New URL rewrite rules for city pages
- **Added**: Enhanced sitemap generation to include all city pages
- **Changed**: Index page generation now filters for state pages only
- **Added**: Automatic link processing functions with collision avoidance
- **Added**: Parent page validation for city creation

### 📚 Documentation
- **Updated**: README.md with comprehensive hierarchical documentation
- **Updated**: Claude.md with dual prompt templates and interlinking system
- **Added**: Hierarchical structure diagrams and URL examples
- **Added**: Complete command reference with all new operations
- **Added**: Enhanced troubleshooting guide for city operations
- **Updated**: Workflow examples for 350-page management

### 🔧 Developer Experience
- **Added**: Enhanced help system with all new commands
- **Added**: Detailed logging for hierarchical operations
- **Added**: Better error messages for missing parent pages
- **Added**: Validation for city-state relationships

---

## [1.0.0] - July 30, 2025

### 🎉 Initial Release

#### Core Features
- **Added**: WordPress plugin for generating SEO-optimized local pages
- **Added**: Claude AI integration using Sonnet 4 model for content generation
- **Added**: Custom "local" post type for WordPress development service pages
- **Added**: WP-CLI integration with comprehensive command structure
- **Added**: Support for all 50 US states with 6 largest cities per state

#### Content Generation
- **Added**: State-specific landing pages (300-400 words each)
- **Added**: WordPress Block Editor (Gutenberg) format support
- **Added**: Automated CTA placement before H2 headings
- **Added**: Professional, factual tone without industry specialization
- **Added**: Geographic relevance through city mentions
- **Added**: Remote-first messaging emphasizing 84EM's distributed approach

#### WP-CLI Commands
- **Added**: `--set-api-key` for secure API key management
- **Added**: `--validate-api-key` for API key verification
- **Added**: `--state=all` for generating all 50 state pages
- **Added**: `--state="StateName"` for individual state generation
- **Added**: `--update --state=all` for bulk updates
- **Added**: `--delete --state=all` for bulk deletion
- **Added**: Multiple state support with comma-delimited lists
- **Added**: `--generate-sitemap` for XML sitemap creation
- **Added**: `--generate-index` for master index page

#### SEO Optimization
- **Added**: Clean URL structure without post type slug
- **Added**: SEO titles and meta descriptions for each state
- **Added**: LD-JSON structured data for LocalBusiness schema
- **Added**: Keyword integration: "WordPress development [State]"
- **Added**: XML sitemap generation for all local pages
- **Added**: Master index page with alphabetized state directory

#### Security & Performance
- **Added**: AES-256-CBC encryption for API key storage
- **Added**: WordPress salts integration for encryption key derivation
- **Added**: Rate limiting with 1-second delays between API requests
- **Added**: Progress tracking with real-time duration monitoring
- **Added**: Comprehensive error handling and logging
- **Added**: Secure API key input without shell history

#### Content Strategy
- **Added**: Service keywords: WordPress development, custom plugins, API integrations
- **Added**: Geographic focus without industry specialization
- **Added**: Call-to-action integration with contact page links
- **Added**: Professional styling with custom CTA blocks
- **Added**: Factual approach suitable for all business types

#### Technical Foundation
- **Added**: WordPress 6.8+ compatibility
- **Added**: PHP 8.2+ requirement
- **Added**: Site restriction to 84em.com domains only
- **Added**: Custom rewrite rules for clean URLs
- **Added**: Post type registration with proper capabilities
- **Added**: Activation/deactivation hooks with rewrite rule flushing

#### Documentation
- **Added**: Comprehensive README.md with setup instructions
- **Added**: Claude.md with prompt templates and guidelines
- **Added**: Installation and configuration documentation
- **Added**: Troubleshooting guide and error handling
- **Added**: API configuration and cost estimates
- **Added**: Workflow examples and best practices

---

## Version Comparison

| Feature | v1.0.0 | v2.0.0 |
|---------|---------|---------|
| **Total Pages** | 50 states | 350 (50 states + 300 cities) |
| **Page Types** | State pages only | Hierarchical states + cities |
| **URL Structure** | `/wordpress-development-services-california/` | `/wordpress-development-services-california/los-angeles/` |
| **Interlinking** | Manual CTAs only | Automatic city names + service keywords |
| **Bulk Commands** | `--state=all` | `--generate-all`, `--update-all` |
| **Content Length** | 300-400 words | States: 300-400, Cities: 250-350 |
| **API Cost (Full)** | $2-4 | $14-28 |
| **Post Type** | Standard | Hierarchical with parent-child |
| **Progress Tracking** | Basic | Comprehensive with statistics |

---

**Maintained by**: 84EM  
**License**: Proprietary  
**WordPress Compatibility**: 6.8+  
**PHP Requirement**: 8.2+