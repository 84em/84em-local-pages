# CLAUDE.md - 84EM Local Pages Content Generation

This document contains the Claude AI prompt templates and guidelines used by the 84EM Local Pages Generator plugin for creating unique, SEO-optimized content for each US state and city.

## Current Prompt Templates (Updated August 2025)

The plugin uses two distinct prompt structures for generating location-specific content:

### State Page Prompt Template

```
Write a concise, SEO-optimized landing page for 84EM's WordPress development services specifically for businesses in {STATE}. 

IMPORTANT: Create unique, original content that is different from other state pages. Focus on local relevance through city mentions and state-specific benefits.

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely.

Include the following key elements:
1. A professional opening paragraph mentioning {STATE} and cities like {CITY_LIST} (6 largest cities)
2. WordPress development services including: {SERVICE_KEYWORDS_LIST}
3. Why businesses in {STATE} choose 84EM (remote expertise, proven track record, reliable delivery)
4. Call-to-action for {STATE} businesses
5. Include naturally-placed keywords: 'WordPress development {STATE}', 'custom plugins {STATE}', 'web development {CITY_LIST}'

Write approximately 300-400 words in a professional, factual tone. Avoid hyperbole and superlatives. Focus on concrete services, technical expertise, and actual capabilities. Make it locally relevant through geographic references while emphasizing 84EM's remote-first approach serves clients nationwide.

CRITICAL: Format the content using WordPress block editor syntax (Gutenberg blocks). Use the following format:
- Paragraphs: <!-- wp:paragraph --><p>Your paragraph text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {"level":2} --><h2><strong>Your Heading</strong></h2><!-- /wp:heading -->
- Sub-headings: <!-- wp:heading {"level":3} --><h3><strong>Your Sub-heading</strong></h3><!-- /wp:heading -->
- Call-to-action links: <a href="/contact/">contact us today</a> or <a href="/contact/">get started</a>

IMPORTANT: 
- All headings (h2, h3) must be wrapped in <strong> tags to ensure they appear bold.
- Include 2-3 call-to-action links throughout the content that link to /contact/ using phrases like "contact us today", "get started", "reach out", "discuss your project", etc.
- Make the call-to-action links natural and contextual within the content.
- Insert this exact CTA block BEFORE every H2 heading:

<!-- wp:group {"className":"get-started-local","style":{"spacing":{"margin":{"top":"0"},"padding":{"bottom":"var:preset|spacing|40","top":"var:preset|spacing|40","right":"0"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group get-started-local" style="margin-top:0;padding-top:var(--wp--preset--spacing--40);padding-right:0;padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:buttons {"className":"animated bounceIn","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons animated bounceIn"><!-- wp:button {"style":{"border":{"radius":{"topLeft":"0px","topRight":"30px","bottomLeft":"30px","bottomRight":"0px"}},"shadow":"var:preset|shadow|crisp"},"fontSize":"large"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-large-font-size has-custom-font-size wp-element-button" href="/contact/" style="border-top-left-radius:0px;border-top-right-radius:30px;border-bottom-left-radius:30px;border-bottom-right-radius:0px;box-shadow:var(--wp--preset--shadow--crisp)">Start Your WordPress Project</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

Do NOT use markdown syntax or plain HTML. Use proper WordPress block markup for all content.
```

### City Page Prompt Template

```
Write a concise, SEO-optimized landing page for 84EM's WordPress development services specifically for businesses in {CITY}, {STATE}. 

IMPORTANT: Create unique, original content that is different from other city pages. Focus on local relevance through city-specific benefits and geographic context.

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely.

Include the following key elements:
1. A professional opening paragraph mentioning {CITY}, {STATE} and local business benefits
2. WordPress development services including: {SERVICE_KEYWORDS_LIST}
3. Why businesses in {CITY} choose 84EM (remote expertise, proven track record, reliable delivery)
4. Call-to-action for {CITY} businesses
5. Include naturally-placed keywords: 'WordPress development {CITY}', 'custom plugins {CITY}', 'web development {STATE}'

Write approximately 250-350 words in a professional, factual tone. Avoid hyperbole and superlatives. Focus on concrete services, technical expertise, and actual capabilities. Make it locally relevant through geographic references while emphasizing 84EM's remote-first approach serves clients nationwide.

CRITICAL: Format the content using WordPress block editor syntax (Gutenberg blocks). Use the following format:
- Paragraphs: <!-- wp:paragraph --><p>Your paragraph text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {"level":2} --><h2><strong>Your Heading</strong></h2><!-- /wp:heading -->
- Sub-headings: <!-- wp:heading {"level":3} --><h3><strong>Your Sub-heading</strong></h3><!-- /wp:heading -->
- Call-to-action links: <a href="/contact/">contact us today</a> or <a href="/contact/">get started</a>

IMPORTANT: 
- All headings (h2, h3) must be wrapped in <strong> tags to ensure they appear bold.
- Include 2-3 call-to-action links throughout the content that link to /contact/ using phrases like "contact us today", "get started", "reach out", "discuss your project", etc.
- Make the call-to-action links natural and contextual within the content.
- Insert this exact CTA block BEFORE every H2 heading:

[Same CTA block markup as state pages]

Do NOT use markdown syntax or plain HTML. Use proper WordPress block markup for all content.
```

## Hierarchical Content Structure

### State Pages (Parent Pages)
- **Content Length**: 300-400 words
- **Geographic Focus**: State and 6 largest cities
- **Automatic Interlinking**: City names link to child city pages
- **Service Keywords**: Link to https://84em.com/contact/
- **URL Format**: `/wordpress-development-services-california/`

### City Pages (Child Pages)
- **Content Length**: 250-350 words
- **Geographic Focus**: Specific city and state context
- **Parent Relationship**: Child of respective state page
- **Service Keywords**: Link to https://84em.com/contact/
- **URL Format**: `/wordpress-development-services-california/los-angeles/`

## Automatic Interlinking System

### State Page Interlinking
The plugin automatically processes state page content after generation:

1. **City Name Detection**: Identifies city names from the state's city list
2. **Link Generation**: Creates URLs in format `/wordpress-development-services-{state}/{city}/`
3. **Content Replacement**: Replaces first occurrence of each city name with link
4. **Service Keyword Linking**: Links service keywords to contextually relevant pages

### City Page Interlinking
City pages receive automatic service keyword linking only:

1. **Service Keyword Detection**: Identifies WordPress development service terms
2. **Smart Link Generation**: Links keywords to appropriate pages based on context:
   - Development work → /work/
   - Service offerings → /services/
   - Project examples → /projects/
   - Location-specific → respective local pages
3. **Single Replacement**: Only first occurrence of each keyword is linked

### Interlinking Implementation (v3.0.0+)
Content processing is handled by the `ContentProcessor` class:
```php
// ContentProcessor handles all content enhancements
$contentProcessor = new ContentProcessor( $keywordsProvider );
$processed = $contentProcessor->processContent( $raw_content, $context );
```

## Dynamic Content Variables

The plugin replaces the following variables in prompts:

### Location Information
- `{STATE}`: Full state name (e.g., "California")
- `{CITY}`: City name (e.g., "Los Angeles") - city pages only
- `{CITY_LIST}`: Comma-separated list of 6 largest cities - state pages only

### Service Keywords
- `{SERVICE_KEYWORDS_LIST}`: WordPress development, custom plugin development, API integrations, security audits, white-label development, WordPress maintenance, WordPress support, data migration, platform transfers, WordPress troubleshooting, custom WordPress themes, WordPress security, web development, WordPress migrations, digital agency services, WordPress plugin development

## Content Structure Guidelines

### WordPress Block Editor Format
All content is generated using proper Gutenberg block syntax:

```html
<!-- wp:paragraph -->
<p>Paragraph content here.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2><strong>Bold Heading</strong></h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3><strong>Bold Sub-heading</strong></h3>
<!-- /wp:heading -->
```

### Call-to-Action Integration

#### Inline CTAs
- **State Pages**: 2-3 contextual links throughout content
- **City Pages**: 2-3 contextual links throughout content
- **Natural Phrases**: "contact us today", "get started", "reach out", "discuss your project"
- **Link Target**: `/contact/` page

#### Prominent CTA Blocks
- **Placement**: Before every H2 heading (automated)
- **Button Text**: "Start Your WordPress Project"
- **Styling**: Custom border radius and shadow effects
- **Layout**: Centered with proper spacing
- **Target**: `/contact/` page

### Remote-First Messaging
- **Emphasis**: 84EM's 100% remote operations
- **Exclusions**: No mentions of on-site visits, local offices, or physical presence
- **Focus**: Remote expertise and proven delivery capabilities
- **Scope**: Nationwide service coverage

### Tone and Style Guidelines
- **Professional, factual tone** - avoid hyperbole and superlatives
- **Technical focus** - emphasize concrete services and capabilities
- **Local relevance** - mention locations naturally within content
- **Concise content** - appropriate word count for page type
- **Generic approach** - suitable for all business types

## SEO Optimization Strategy

### Keyword Integration

**State Pages:**
- Primary: "WordPress development {STATE}"
- Secondary: "custom plugins {STATE}"
- Location-based: "web development {CITY_LIST}"

**City Pages:**
- Primary: "WordPress development {CITY}"
- Secondary: "custom plugins {CITY}"
- Location-based: "web development {STATE}"

### Meta Data Structure

**State Pages:**
- **Title**: "Expert WordPress Development Services in {STATE} | 84EM"
- **Meta Description**: "Professional WordPress development, custom plugins, and web solutions for businesses in {STATE}. White-label services for agencies in {CITY_1}, {CITY_2}, {CITY_3}."

**City Pages:**
- **Title**: "Expert WordPress Development Services in {CITY}, {STATE} | 84EM"
- **Meta Description**: "Professional WordPress development, custom plugins, and web solutions for businesses in {CITY}, {STATE}. White-label services and expert support."

### URL Structure
Clean hierarchical URLs without post type slug:

**State Pages:**
- Format: `/wordpress-development-services-{state}/`
- Example: `/wordpress-development-services-california/`

**City Pages:**
- Format: `/wordpress-development-services-{state}/{city}/`
- Example: `/wordpress-development-services-california/los-angeles/`

### LD-JSON Schema

**State Pages:**
- Type: LocalBusiness
- Service Area: State with city containment
- Contains Place: Array of major cities

**City Pages:**
- Type: LocalBusiness
- Service Area: Specific city
- Contained In Place: Parent state
- Address Locality: City name

## API Configuration

### Current Model Settings (v3.0.0+)
```php
// Located in src/Api/ClaudeApiClient.php
private const MODEL = 'claude-sonnet-4-20250514';
private const MAX_TOKENS = 4000;
private const TIMEOUT = 60;
private const API_VERSION = '2023-06-01';
```

### Rate Limiting
- **Delay Between Requests**: 1 second minimum
- **Timeout**: 60 seconds per request
- **Progress Tracking**: Real-time duration monitoring
- **Bulk Operations**: Progress bars with comprehensive statistics

## WP-CLI Command Structure

### Bulk Operations
```bash
# Generate everything (350 pages: 50 states + 300 cities)
wp 84em local-pages --generate-all

# Generate states only (50 pages)
wp 84em local-pages --generate-all --states-only

# Update all existing pages
wp 84em local-pages --update-all

# Update existing states only
wp 84em local-pages --update-all --states-only
```

### Individual Operations
```bash
# State operations
wp 84em local-pages --state="California"
wp 84em local-pages --state=all

# City operations
wp 84em local-pages --state="California" --city=all
wp 84em local-pages --state="California" --city="Los Angeles"
wp 84em local-pages --state="California" --city="Los Angeles,San Diego"

# Generate all cities AND update state page
wp 84em local-pages --state="California" --city=all --complete
```

### Supporting Operations
```bash
# Generate index page (no API key required)
wp 84em local-pages --generate-index

# Generate XML sitemap (no API key required)
wp 84em local-pages --generate-sitemap

# Regenerate LD-JSON schemas without touching content (no API key required)
wp 84em local-pages --regenerate-schema                    # All pages
wp 84em local-pages --regenerate-schema --states-only      # States only
wp 84em local-pages --regenerate-schema --state="California"  # Specific state and its cities
wp 84em local-pages --regenerate-schema --state="California" --state-only  # State only, no cities
wp 84em local-pages --regenerate-schema --state="California" --city="Los Angeles"  # Specific city
```

## Content Quality Assurance

### Automated Checks
- WordPress block syntax validation
- Keyword density monitoring
- Character count verification
- CTA placement verification
- Automatic interlinking processing

### Manual Review Points
1. **Geographic Relevance**: Natural mention of locations
2. **Hierarchical Structure**: Parent-child relationships maintained
3. **Natural Keyword Integration**: No keyword stuffing
4. **Service Focus**: Technical capabilities without industry claims
5. **Local Authenticity**: Location names feel natural
6. **Professional Tone**: Factual without exaggeration
7. **Clear CTAs**: Multiple conversion opportunities
8. **Block Structure**: Proper Gutenberg formatting
9. **Interlinking**: City names link to city pages, keywords to contact

## Performance Monitoring

### Content Metrics
- Organic search rankings for target keywords
- Page engagement metrics (time on page, bounce rate)
- Conversion rates from CTAs
- Internal link click-through rates
- Hierarchical navigation patterns

### Technical Metrics
- API response times (tracked with duration display)
- Content generation success rates
- WordPress block parsing accuracy
- SEO meta data completeness
- XML sitemap generation and validation
- Automatic interlinking accuracy

## Troubleshooting

### Common Content Issues

#### Block Syntax Errors
**Problem**: Malformed WordPress blocks
**Solution**: Verify exact block markup in prompt templates

#### Missing CTAs
**Problem**: CTA blocks not appearing before H2s
**Solution**: Check H2 detection and CTA insertion logic

#### Generic Content
**Problem**: Similar content across locations
**Solution**: Strengthen geographic relevance and location mentions

#### Missing Interlinking
**Problem**: City names or keywords not linked
**Solution**: Verify automatic linking functions and content processing

### Hierarchical Issues

#### Parent-Child Relationships
**Problem**: City pages not properly linked to state pages
**Solution**: Ensure state page exists before creating city pages

#### URL Structure
**Problem**: Incorrect hierarchical URLs
**Solution**: Verify rewrite rules and post_parent relationships

### API Issues

#### Timeout Errors
**Problem**: Requests exceeding 60-second limit
**Solution**: Check network connectivity and API status

#### Rate Limiting
**Problem**: Too many requests too quickly
**Solution**: Verify 1-second delay between requests

#### Model Errors
**Problem**: Unexpected Claude model responses
**Solution**: Verify API key and model availability

## Commands Not Using Claude AI

### Index Page Generation
The `--generate-index` command creates a master index page with an alphabetized list of states. This command:
- **Does not require Claude API key**: Uses only existing state page data
- **No API calls**: Content is generated programmatically using WordPress block syntax
- **Static content**: Uses predefined template with dynamic state list from WP_Query
- **Page details**: Creates/updates `wordpress-development-services-usa` page
- **State Focus**: Only lists state pages, not city pages

### Sitemap Generation
The `--generate-sitemap` command creates XML sitemaps. This command:
- **Does not require Claude API key**: Uses only existing local page data
- **No API calls**: Generates XML using WordPress permalink data
- **Includes All Pages**: Both state and city pages in sitemap
- **Static output**: Creates `sitemap-local.xml` in WordPress root directory

## Plugin Architecture (v3.0.0+)

### Modular Structure
The plugin has been refactored from a monolithic class to a modern modular architecture:

#### Core Components
- **`Plugin`**: Main plugin class handling initialization and service registration
- **`Container`**: Dependency injection container for managing class instances
- **`PostTypes/LocalPostType`**: Manages the custom post type registration and rewrite rules

#### API Layer (`src/Api/`)
- **`ApiKeyManager`**: Handles API key storage and retrieval
- **`ClaudeApiClient`**: Manages communication with Claude API
- **`Encryption`**: Provides AES-256-CBC encryption for API keys

#### CLI Layer (`src/Cli/`)
- **`CommandHandler`**: Main WP-CLI command registration and routing
- **`Commands/GenerateCommand`**: Handles all content generation commands
- **`Commands/TestCommand`**: Manages the testing framework

#### Content Layer (`src/Content/`)
- **`StateContentGenerator`**: Generates state page content
- **`CityContentGenerator`**: Generates city page content

#### Data Layer (`src/Data/`)
- **`StatesProvider`**: Provides US states and cities data
- **`KeywordsProvider`**: Manages service keywords and URLs

#### Schema Layer (`src/Schema/`)
- **`SchemaGenerator`**: Creates LD-JSON structured data

#### Utils Layer (`src/Utils/`)
- **`ContentProcessor`**: Handles content processing, linking, and formatting

### Key Classes and Responsibilities

| Class | Responsibility | Location |
|-------|---------------|----------|
| `Plugin` | Main initialization | `src/Plugin.php` |
| `ApiKeyManager` | API key management | `src/Api/ApiKeyManager.php` |
| `ClaudeApiClient` | Claude API communication | `src/Api/ClaudeApiClient.php` |
| `StateContentGenerator` | State page generation | `src/Content/StateContentGenerator.php` |
| `CityContentGenerator` | City page generation | `src/Content/CityContentGenerator.php` |
| `ContentProcessor` | Content enhancement | `src/Utils/ContentProcessor.php` |
| `CommandHandler` | CLI command routing | `src/Cli/CommandHandler.php` |

### Namespace Structure
All classes use the `EightyFourEM\LocalPages` namespace:
```php
namespace EightyFourEM\LocalPages\Api;
namespace EightyFourEM\LocalPages\Cli;
namespace EightyFourEM\LocalPages\Content;
// etc.
```

## Recent Updates (v3.0.1)

### Bug Fixes
1. **State Page Query Fix**: Fixed issue where state commands were incorrectly updating city pages instead of state pages. The query now properly checks for `_local_page_city` meta NOT EXISTS to ensure state pages are correctly identified.

2. **City Interlinking Fix**: City names in state pages are now properly hyperlinked to their respective city pages. The fix involved:
   - Processing location links BEFORE service keywords to prevent keyword conflicts
   - Passing cities context to ContentProcessor for state pages
   - Removing hardcoded location-specific keywords that interfered with dynamic linking

3. **Block Editor Compatibility Fix**: Fixed invalid WordPress block structure that prevented editing in the Block Editor:
   - ContentProcessor now detects if content already has WordPress block markup
   - Prevents double-wrapping of blocks which was creating nested/malformed structures
   - Content from Claude API (already with blocks) is no longer re-wrapped
   - Added tests to prevent regression of this issue

4. **Service Keyword Linking Fix**: Fixed issues with multi-word keywords not being linked:
   - Improved regex pattern for better word boundary detection
   - Preserves original case from content when creating links
   - Fixed handling of keywords like "API integrations" and "security audits"

### New Features
1. **Complete State Generation**: New `--complete` flag for generating all cities AND updating the state page in one command:
   ```bash
   wp 84em local-pages --state="California" --city=all --complete
   ```

---

**Last Updated**: August 15, 2025  
**Claude Model**: claude-sonnet-4-20250514  
**Content Format**: WordPress Block Editor (Gutenberg)  
**API Version**: 2023-06-01  
**Content Strategy**: Hierarchical location pages with automatic interlinking  
**Total Pages**: 350 (50 states + 300 cities)  
**Plugin Version**: 3.0.1  
**Architecture**: Modular PSR-4 autoloaded classes with dependency injection
