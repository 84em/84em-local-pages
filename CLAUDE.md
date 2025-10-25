# CLAUDE.md - 84EM Local Pages Content Generation

This document contains the Claude AI prompt templates and guidelines used by the 84EM Local Pages Generator plugin for creating unique, SEO-optimized content for each US state and city.

## Current Prompt Templates (Updated October 2025 - v3.4.0)

The plugin uses two distinct prompt structures for generating location-specific content with improved readability using list-based formatting and concise sentence structures.

### State Page Prompt Template

```
Write a concise, SEO-optimized landing page for 84EM's WordPress development services specifically for businesses in {STATE}.

IMPORTANT: Create unique, original content that is different from other state pages. Focus on local relevance through city mentions and state-specific benefits.

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely. But DO mention that 84EM is headquartered in Cedar Rapids, Iowa. No need to specifically use the phrase "remote-first".

CONTENT STRUCTURE (REQUIRED):

**Opening Section (3-4 short sentences, one per line)**
- Professional introduction mentioning {STATE} and ALL of these cities: {CITY_LIST} (you MUST mention all 6 cities naturally)
- Brief overview of 84EM's WordPress expertise
- Include ONE contextual call-to-action link in the opening

**Core Services Section (H2: "WordPress Development Services in {STATE}")**
Present services in an UNORDERED LIST using WordPress block syntax:
<!-- wp:list -->
<ul>
<li>Service name with brief 5-8 word benefit-focused description</li>
<li>Service name with brief 5-8 word benefit-focused description</li>
</ul>
<!-- /wp:list -->

Include these services from the list: {SERVICE_KEYWORDS_LIST}
Select 8-10 most relevant services and present as list items. Keep descriptions concise and focused on business benefits, NOT keyword-stuffed.

**Why Choose 84EM Section (H2: "Why {STATE} Businesses Choose 84EM")**
Present 4-5 key benefits as an UNORDERED LIST:
<!-- wp:list -->
<ul>
<li>Fully remote team serving clients nationwide with proven processes</li>
<li>30 years of combined WordPress development experience</li>
<li>Proven track record across diverse industries</li>
<li>Reliable delivery with consistent communication</li>
<li>Scalable solutions designed to grow with your business</li>
</ul>
<!-- /wp:list -->

**Closing Paragraph**
- 2 sentences emphasizing local relevance across {STATE} and 84EM's headquarters in Cedar Rapids, Iowa, with each sentence on their own line
- Strong call-to-action with contact link
- Mention several cities from the list: {CITY_LIST}

IMPORTANT GRAMMAR RULES:
- Use proper prepositions (in, for, near) when mentioning locations
- Never use city/state names as adjectives directly before service terms (avoid "{STATE} solutions")
- Correct: "businesses in {STATE}", "services for {STATE} companies", "development in {STATE}"
- Incorrect: "{STATE} businesses seeking {STATE} solutions"

TARGET METRICS:
- Total word count: 200-300 words
- Opening: 3-4 short sentences, each on their own line
- Services: 8-10 list items with brief descriptions
- Benefits: 4-5 list items
- Closing: 2 sentences, each on their own line
- Call-to-action links: 2-3 total (contextual, not in lists)
- City mentions: All 6 cities mentioned at least once

TONE: Professional and factual. Avoid hyperbole and superlatives. Focus on concrete services, technical expertise, and actual capabilities. Make it locally relevant through geographic references.

CRITICAL: Format the content using WordPress block editor syntax (Gutenberg blocks). Use the following format:
- Paragraphs: <!-- wp:paragraph --><p>Your paragraph text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {"level":2} --><h2><strong>Your Heading</strong></h2><!-- /wp:heading -->
- Lists: <!-- wp:list --><ul><li>Item text here</li><li>Item text here</li></ul><!-- /wp:list -->
- Call-to-action links: <a href="/contact/">contact us today</a> or <a href="/contact/">get started</a>

IMPORTANT:
- All headings (h2, h3) must be wrapped in <strong> tags to ensure they appear bold.
- Include 2-3 call-to-action links throughout the content that link to /contact/ using phrases like "contact us today", "get started", "reach out", "discuss your project", etc.
- Make the call-to-action links natural and contextual within PARAGRAPH content (not within list items).
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

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely. But DO mention that 84EM is headquartered in Cedar Rapids, Iowa. No need to specifically use the phrase "remote-first".

CONTENT STRUCTURE (REQUIRED):

**Opening Section (3-4 short sentences, one per line)**
- Professional introduction mentioning {CITY}, {STATE} and local business context
- Brief overview of 84EM's WordPress expertise
- Include ONE contextual call-to-action link in the opening

**Core Services Section (H2: "WordPress Development Services in {CITY}")**
Present services in an UNORDERED LIST using WordPress block syntax:
<!-- wp:list -->
<ul>
<li>Service name with brief 5-8 word benefit-focused description</li>
<li>Service name with brief 5-8 word benefit-focused description</li>
</ul>
<!-- /wp:list -->

Include these services from the list: {SERVICE_KEYWORDS_LIST}
Select 6-8 most relevant services and present as list items. Keep descriptions concise and focused on business benefits, NOT keyword-stuffed.

**Why Choose 84EM Section (H2: "Why {CITY} Businesses Choose 84EM")**
Present 3-4 key benefits as an UNORDERED LIST:
<!-- wp:list -->
<ul>
<li>Fully remote team serving clients nationwide with proven processes</li>
<li>30 years of combined WordPress development experience</li>
<li>Proven track record across diverse industries</li>
<li>Scalable solutions designed to grow with your business</li>
</ul>
<!-- /wp:list -->

**Closing Paragraph**
- 2 sentences, each on their own line, emphasizing local relevance and 84EM's headquarters in Cedar Rapids, Iowa
- Strong call-to-action with contact link
- Mention web development in {STATE}

IMPORTANT GRAMMAR RULES:
- Use proper prepositions (in, for, near) when mentioning locations
- Never use city/state names as adjectives directly before service terms (avoid "{CITY} solutions")
- Correct: "businesses in {CITY}", "services for {CITY} companies", "development in {CITY}"
- Incorrect: "{CITY} businesses seeking {CITY} solutions"

TARGET METRICS:
- Total word count: 200-300 words
- Opening: 3-4 short sentences, each on their own line
- Services: 6-8 list items with brief descriptions
- Benefits: 3-4 list items
- Closing: 2 sentences, each on their own line
- Call-to-action links: 2-3 total (contextual, not in lists)

TONE: Professional and factual. Avoid hyperbole and superlatives. Focus on concrete services, technical expertise, and actual capabilities. Make it locally relevant through geographic references.

CRITICAL: Format the content using WordPress block editor syntax (Gutenberg blocks). Use the following format:
- Paragraphs: <!-- wp:paragraph --><p>Your paragraph text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {"level":2} --><h2><strong>Your Heading</strong></h2><!-- /wp:heading -->
- Lists: <!-- wp:list --><ul><li>Item text here</li><li>Item text here</li></ul><!-- /wp:list -->
- Call-to-action links: <a href="/contact/">contact us today</a> or <a href="/contact/">get started</a>

IMPORTANT:
- All headings (h2, h3) must be wrapped in <strong> tags to ensure they appear bold.
- Include 2-3 call-to-action links throughout the content that link to /contact/ using phrases like "contact us today", "get started", "reach out", "discuss your project", etc.
- Make the call-to-action links natural and contextual within PARAGRAPH content (not within list items).
- Insert this exact CTA block BEFORE every H2 heading:

[Same CTA block markup as state pages]

Do NOT use markdown syntax or plain HTML. Use proper WordPress block markup for all content.
```

## Hierarchical Content Structure

### State Pages (Parent Pages)
- **Content Length**: 200-300 words
- **Geographic Focus**: State and 6 largest cities
- **Automatic Interlinking**: City names link to child city pages
- **Service Keywords**: Link to relevant service pages using fuzzy matching
- **URL Format**: `/wordpress-development-services-california/`

### City Pages (Child Pages)
- **Content Length**: 200-300 words
- **Geographic Focus**: Specific city and state context
- **Parent Relationship**: Child of respective state page
- **Service Keywords**: Link to relevant service pages using fuzzy matching
- **URL Format**: `/wordpress-development-services-california/los-angeles/`

## Automatic Interlinking System

### State Page Interlinking
The plugin automatically processes state page content after generation:

1. **City Name Detection**: Identifies city names from the state's city list
2. **Link Generation**: Creates URLs in format `/wordpress-development-services-{state}/{city}/`
3. **Content Replacement**: Replaces first occurrence of each city name with link
4. **Service Keyword Linking**: Links service keywords using intelligent fuzzy matching

### City Page Interlinking
City pages receive automatic service keyword linking only:

1. **Service Keyword Detection**: Identifies WordPress development service terms using fuzzy matching
2. **Smart Link Generation**: Links keywords to appropriate pages based on context:
   - Custom plugin development → /services/custom-wordpress-plugin-development/
   - White-label development → /services/white-label-wordpress-development-for-agencies/
   - General services → /services/
   - Development work → /work/
3. **Fuzzy Matching**: Intelligently matches service text to keywords even with variation

### Fuzzy Keyword Matching (v3.4.0+)

The plugin uses intelligent fuzzy matching to ensure every service list item gets linked:

**How It Works:**
1. **List Item Scanning**: Extracts all list items from generated content
2. **Keyword Searching**: For each list item, searches for ALL matching keywords (case-insensitive substring match)
3. **Best Match Selection**: Selects the longest/most specific matching keyword
4. **Link Insertion**: Links the matched keyword within the list item, preserving original case

**Benefits:**
- Works with any API-generated text that contains keyword substrings
- Ensures maximum link coverage in service lists
- Handles variations in capitalization and phrasing
- Prioritizes more specific keywords when multiple matches found

**Example:**
- List item: "Custom WordPress Development – Tailored solutions..."
- Matches: "WordPress development", "Custom WordPress development"
- Selected: "Custom WordPress development" (longer/more specific)
- Result: `<a href="...">Custom WordPress development</a> – Tailored solutions...`

**Safeguards (v3.6.1+):**
1. **Protected Service Lists**: List items containing `<strong>` tags are completely protected from automatic keyword linking using placeholder replacement
2. **Tag-Aware Linking**: Content is split by HTML tags before keyword matching to prevent linking text inside href attributes or other tag attributes
3. **Prevent Double-Linking**: List items that already contain `<a href=` tags are skipped by the list-item-specific linking logic
4. **Preserve Hardcoded Structure**: Both state and city page prompts include hardcoded service lists with "Learn More →" links that are protected from modification

### Interlinking Implementation (v3.4.0+)
Content processing is handled by the `ContentProcessor` class:
```php
// ContentProcessor handles all content enhancements including fuzzy matching
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
- `{SERVICE_KEYWORDS_LIST}`: WordPress development, custom plugin development, Custom WordPress development, Data migration and platform transfers, WordPress security audits and hardening, WordPress Maintenance and Support, API integrations, WordPress security audits, security audits, white-label development, White Label Development, WordPress maintenance, WordPress support, data migration, platform transfers, Platform Migrations, WordPress maintenance and ongoing support, WordPress troubleshooting, custom WordPress themes, WordPress security, web development, WordPress migrations, digital agency services, WordPress plugin development, Custom WordPress plugin development, White label WordPress development, White label web development, White-label development services for agencies, WordPress plugin development services

**Note**: The expanded keyword list (v3.4.0+) includes multiple variations and capitalizations to improve fuzzy matching coverage. The interlinking system will automatically select the most specific/relevant match for each service list item.

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

### Current Model Settings (v3.2.4+)
```php
// Located in src/Api/ClaudeApiClient.php
private const MAX_TOKENS = 4000;
private const TIMEOUT = 600;  // 10 minutes
private const API_VERSION = '2023-06-01';
private const MAX_RETRIES = 5;  // Retry failed requests with exponential backoff
private const INITIAL_RETRY_DELAY = 1;  // Initial delay between retries
private const MODELS_ENDPOINT = 'https://api.anthropic.com/v1/models';
```

### Model Configuration (v3.2.4+)

The plugin uses dynamic model selection fetched directly from the Claude API:

#### Model Selection Process
1. User runs `--set-api-model` command
2. Plugin fetches available models from Claude API
3. User selects model from interactive numbered list
4. Model is validated with test API call
5. If validation succeeds, model is saved

#### Model Management Commands

```bash
# Set/update model (fetches list from Claude API)
wp 84em local-pages --set-api-model

# View current model configuration
wp 84em local-pages --get-api-model

# Validate current model
wp 84em local-pages --validate-api-model

# Clear current model configuration
wp 84em local-pages --reset-api-model
```

#### Model Validation
All model changes are validated with a test API call before being saved. This ensures:
- The model exists and is accessible
- Your API key has permission to use the model
- The model is functioning correctly

If validation fails, the model will NOT be saved and you'll see a clear error message.

#### Model Storage
- Models are stored in WordPress option: `84em_local_pages_claude_api_model`
- **No default model** - users must select a model before generating content
- Model configuration is separate from API key storage
- Models can be changed at any time via WP-CLI
- Available models are fetched dynamically from Claude's Models API

### Rate Limiting and Error Handling
- **Delay Between Requests**: 1 second minimum
- **Timeout**: 600 seconds (10 minutes) per request
- **Retry Logic**: Up to 5 attempts with exponential backoff for transient errors
- **Retryable Errors**: Network issues, rate limits, server errors (500-503, 529)
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

# Update keyword links in existing pages (no API key required)
wp 84em local-pages --update-keyword-links                    # All pages
wp 84em local-pages --update-keyword-links --states-only      # States only

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
**Problem**: Requests exceeding 600-second (10 minute) limit
**Solution**: Check network connectivity and API status. The plugin will automatically retry up to 3 times with exponential backoff for transient errors

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

### Keyword Link Updates
The `--update-keyword-links` command refreshes all keyword and location links in existing pages. This command:
- **Does not require Claude API key**: Works with existing page content
- **No API calls**: Reprocesses existing content with current keyword mappings
- **Use Case**: Update links when KeywordsProvider URLs change
- **Process**: 
  1. Strips existing auto-generated keyword and location links
  2. Reprocesses content with ContentProcessor using latest keywords
  3. Preserves user-added links and content structure
- **Options**:
  - `--update-keyword-links`: Updates all state and city pages
  - `--update-keyword-links --states-only`: Updates state pages only
- **Performance**: Uses progress bar and batch processing for efficiency

## Health Check Endpoint

The plugin provides a REST API health check endpoint for deployment verification:

### Endpoint
```
GET /wp-json/84em-local-pages/v1/health
```

### Response
```json
{
    "status": "ok"
}
```

### Purpose
- Verify plugin is active after deployment
- Used by GitHub Actions deployment workflows
- Returns HTTP 200 if plugin is functioning
- Minimal response for security (no system information exposed)

## Plugin Architecture (v3.0.0+)

### Modular Structure
The plugin has been refactored from a monolithic class to a modern modular architecture:

#### Core Components
- **`Plugin`**: Main plugin class handling initialization and service registration
- **`Container`**: Dependency injection container for managing class instances
- **`PostTypes/LocalPostType`**: Manages the custom post type registration and rewrite rules

#### API Layer (`src/Api/`)
- **`ApiKeyManager`**: Handles API key storage and retrieval
- **`ClaudeApiClient`**: Manages communication with Claude API (includes retry logic and error handling)
- **`Encryption`**: Provides AES-256-CBC encryption for API keys
- **`HealthCheckEndpoint`**: REST API endpoint for deployment verification

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

## Testing Framework

The plugin includes a comprehensive WP-CLI-based testing framework that uses **real WordPress functions and API calls** instead of mocks, following WordPress best practices.

### Running Tests
```bash
# Run all test suites
wp 84em local-pages --test --all

# Run specific test suite
wp 84em local-pages --test --suite=api-client
```

### Test Configuration

**Tests always use real WordPress functions, real database operations, and real API calls.** There are no mocks.

The test suite will use the production Claude API key that's already configured in the plugin. If you want to use a different API key for testing (to keep test API usage separate from production), you can set:

```bash
# Optional: Use a different API key for testing
export EIGHTYFOUREM_TEST_API_KEY="your-test-api-key-here"
```

If not set, tests will use the production API key configured in the plugin (stored encrypted in `84em_local_pages_claude_api_key_encrypted`).

### Available Test Suites (v3.2.5)
- **encryption** - API key encryption and security
- **data-structures** - Service keywords and states data
- **content-processing** - Content processing and linking
- **cli-args** - WP-CLI argument parsing
- **ld-json** - Schema.org structured data
- **api-client** - Claude API client with retry logic (no mocks)
- **content-generators** - State and city content generation (no mocks)
- **error-handling** - Error handling and recovery
- **security** - Security and input sanitization
- **model-management** - Model configuration and validation

**Total**: 10 test suites with 82 tests

**Testing Philosophy**: All tests use real WordPress functions (get_option, update_option, delete_option) and real class instances. No mocks, no anonymous classes, just real integration testing. Tests properly clean up after themselves by restoring original database values.

For detailed testing documentation, see [TESTING.md](TESTING.md).

## Recent Updates

### Version 3.6.1 (2025-10-25)

#### Enhanced Keyword Linking Safeguards
- **Problem Solved**: Automatic keyword linking was adding unwanted links to bolded service titles, creating nested links within href attributes, and double-linking existing content
- **Solution**: Implemented comprehensive protection for service list items
  1. **List Item Protection**: List items containing `<strong>` tags are completely protected from automatic linking via placeholder replacement
  2. **Tag-Aware Splitting**: Content is split by HTML tags before linking to prevent matches inside tag attributes
  3. **Clean Prompt Structure**: Removed `<strong>` tags from "Learn More →" links in prompts (keeping only service category titles bolded)
- **Benefits**:
  - Preserves hardcoded service lists exactly as specified in prompts
  - Prevents nested links and malformed HTML
  - Maintains clean, readable service lists with only the intended "Learn More →" links
  - Works for both state and city pages
- **Updated Files**:
  - `src/Utils/ContentProcessor.php` - Added placeholder-based protection for list items with `<strong>` tags
  - `src/Content/StateContentGenerator.php` - Removed `<strong>` tags from "Learn More →" links
  - `src/Content/CityContentGenerator.php` - Removed `<strong>` tags from "Learn More →" links, fixed HTML typo
  - `CLAUDE.md` - Updated documentation with new safeguards
- **Technical Implementation**:
  - Protected list items are replaced with placeholders before keyword linking
  - Content is split by HTML tags to avoid matching text inside attributes
  - Placeholders are restored after all keyword linking is complete
  - Service category titles remain bolded while "Learn More →" links are unbolded for cleaner appearance

### Version 3.3.0 (2025-10-19)

#### Enhanced Content Readability with List-Based Structure
- **Problem Solved**: Previous content was dense "walls of text" with services and benefits crammed into paragraphs
- **New Approach**: List-based formatting for improved scannability and readability
- **Content Structure Changes**:
  - **State Pages**: 8-10 service list items, 4-5 benefit list items
  - **City Pages**: 6-8 service list items, 3-4 benefit list items
  - Short paragraphs (2-3 sentences maximum)
  - Lists use proper WordPress `<!-- wp:list -->` block syntax
- **Benefits**:
  - Improved scannability for users with short attention spans
  - Reduced cognitive load through chunked information
  - Better mobile experience with vertical lists
  - Maintained SEO value with all keywords present
  - Professional service page appearance
- **Updated Files**:
  - `src/Content/CityContentGenerator.php` - Updated buildPrompt() method
  - `src/Content/StateContentGenerator.php` - Updated buildPrompt() method
  - `CLAUDE.md` - Updated prompt template documentation

#### Test Classification: Integration Tests
- **Renamed Test Suite**: Changed from "unit tests" to "integration tests" for accuracy
  - Renamed `tests/unit/` directory to `tests/integration/`
  - Updated all test file docblocks to reflect integration testing terminology
  - Updated `TestCommand.php` to reference new directory path
  - Updated `TESTING.md` and `README.md` documentation with correct terminology
- **Why Integration Tests**: Tests use real WordPress database, real API calls to Claude, and real class instances (no mocks)
- **Removed Obsolete Mocks**: Deleted unused `tests/fixtures/mock-api-responses.php` and `tests/wp-mocks.php` files
- **All 82 Integration Tests Pass**: 100% success rate with valid API key configured

### Version 3.2.5 (2025-10-19)

#### Test Suite Refactor: Consistent ApiKeyManager Usage
- **ApiKeyManager Methods**: All tests now use `ApiKeyManager` methods exclusively
  - Replaced all direct `get_option()`, `update_option()`, `delete_option()` calls
  - Tests use `getKey()`, `setKey()`, `deleteKey()`, `getModel()`, `setModel()`, `deleteModel()`
  - Properly respects `getOptionName()` method which handles test prefix logic
- **Real WordPress Integration**: All tests use real WordPress functions, no mocks
- **Real API Calls**: Tests make real API calls to Claude using production API key
- **Test Data Isolation**: Complete separation via `test_` prefixed options
- **Proper Cleanup**: All tests restore original database values in tearDown()
- **All 82 Tests Pass**: 100% success rate with valid API key configured

This refactor ensures all tests follow the global CLAUDE.md guideline: "don't use mocks, always use real wordpress functions, api calls, etc." while maintaining proper encapsulation through the ApiKeyManager interface.

### Version 3.2.0 (2025-08-17)

#### Dependency Injection Overhaul
- **Eliminated Service Locator Anti-Pattern**: All classes now use proper constructor injection
- **Singleton Services**: `ClaudeApiClient` registered as singleton for better performance
- **Real WordPress Integration**: Tests use real WordPress functions and API calls, not mocks
- **Clean Architecture**: Following SOLID principles throughout

#### CLI Argument Validation
- **Smart Error Detection**: Catches missing `--` prefixes and suggests corrections
- **Typo Detection**: Uses Levenshtein distance for intelligent suggestions
- **Argument Validation**: Ensures required arguments are present
- **Helpful Error Messages**: Clear examples of correct usage

#### Test Suite Optimization
- **Reduced Test Count**: From 120 to 69 tests (42% reduction)
- **Focused Testing**: Removed tests for PHP built-ins and WordPress core
- **9 Test Suites**: Down from 10 (removed unnecessary container tests)

### Version 3.1.2

### Enhanced Error Handling
- **Retry Logic**: API calls now retry up to 3 times with exponential backoff
- **Smart Error Classification**: Distinguishes between retryable and permanent errors
- **Rate Limit Handling**: Respects Retry-After headers from API
- **Comprehensive Logging**: Multi-level logging (Error, Warning, Info) for debugging

### Health Check Endpoint
- **Simple REST API**: `/wp-json/84em-local-pages/v1/health`
- **Minimal Response**: Returns only `{"status": "ok"}` for security
- **Deployment Verification**: Used by GitHub Actions workflows

### Testing Improvements
- **Full Test Coverage**: All test suites now execute completely
- **Context-Aware Output**: Suppresses expected warnings during tests
- **Extended TestCase**: Added missing assertion methods

## Version History

For a complete list of changes, bug fixes, and new features, see [CHANGELOG.md](CHANGELOG.md).

---

**Last Updated**: October 25, 2025
**Claude Model**: claude-sonnet-4-20250514
**Content Format**: WordPress Block Editor (Gutenberg) with concise sentence-per-line structure
**API Version**: 2023-06-01
**Content Strategy**: Hierarchical location pages with fuzzy-matched keyword linking
**Word Count**: 200-300 words per page (both states and cities)
**Total Pages**: 350 (50 states + 300 cities)
**Plugin Version**: 3.6.1
**Architecture**: Modular PSR-4 autoloaded classes with dependency injection
**Model Selection**: Dynamic fetching from Claude Models API with interactive selection
**Keyword Matching**: Intelligent fuzzy matching with safeguards to prevent linking bolded titles and double-linking
**Testing**: Real WordPress integration, no mocks, 82 integration tests (100% passing with valid API key)

- Always ensure the CLAUDE.md is up to date.
- Always ensure the README.md is up to date.
- Always ensure TESTING.md is up to date after any change to the test framework.