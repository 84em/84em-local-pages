# Claude.md - 84EM Local Pages Content Generation

This document contains the Claude AI prompt templates and guidelines used by the 84EM Local Pages Generator plugin for creating unique, SEO-optimized content for each US state.

## Current Prompt Template (Updated January 2025)

The plugin uses the following dynamic prompt structure for generating state-specific content:

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

Write approximately 480-640 words in a professional, factual tone. Avoid hyperbole and superlatives. Focus on concrete services, technical expertise, and actual capabilities. Make it locally relevant through geographic references while emphasizing 84EM's remote-first approach serves clients nationwide.

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

## Content Strategy Changes (January 2025)

### Removed Industry Specialization
- **No longer used**: State-specific industry angles or specializations
- **Removed**: Industry-focused unique angles for each state
- **Focus**: Generic WordPress development services applicable to all business types
- **Uniqueness**: Achieved through geographic relevance and city mentions only

### Simplified Content Structure
The content generation now follows a streamlined approach:

1. **Geographic Relevance**: State and major city mentions for local SEO
2. **Service Focus**: WordPress development capabilities and technical expertise
3. **Remote Emphasis**: 100% remote operations and nationwide service
4. **Clear CTAs**: Multiple conversion opportunities throughout content

## Dynamic Content Variables

The plugin replaces the following variables in the prompt:

### State Information
- `{STATE}`: Full state name (e.g., "California")
- `{CITY_LIST}`: Comma-separated list of 6 largest cities

### Service Keywords
- `{SERVICE_KEYWORDS_LIST}`: WordPress development, custom plugin development, API integrations, security audits, white-label development, WordPress maintenance, WordPress support, data migration, platform transfers, WordPress troubleshooting, custom WordPress themes, WordPress security, web development, digital agency services

### Removed Variables
- `{UNIQUE_ANGLE}`: No longer used (removed industry specialization)
- `{WORK_KEYWORDS_LIST}`: No longer used (removed industry mentions)

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
- 2-3 contextual links throughout content
- Natural phrases: "contact us today", "get started", "reach out", "discuss your project"
- All link to `/contact/` page

#### Prominent CTA Blocks
- Placed before every H2 heading (automated)
- Styled button with "Start Your WordPress Project" text
- Custom styling with border radius and shadow effects
- Centered layout with proper spacing

### Remote-First Messaging
- Emphasizes 84EM's 100% remote operations
- No mentions of on-site visits, local offices, or physical presence
- Focus on remote expertise and proven delivery
- Nationwide service capability

### Tone and Style Guidelines
- **Professional, factual tone** - avoid hyperbole and superlatives
- **Technical focus** - emphasize concrete services and capabilities
- **Local relevance** - mention state and cities naturally
- **Concise content** - 480-640 words (reduced 20% from previous versions)
- **No industry claims** - generic approach suitable for all business types

## SEO Optimization Strategy

### Keyword Integration
Each page naturally incorporates:
- Primary: "WordPress development {STATE}"
- Secondary: "custom plugins {STATE}"
- Location-based: "web development {CITY_LIST}"

### Meta Data Structure
- **Title**: "Expert WordPress Development Services in {STATE} | 84EM"
- **Meta Description**: "Professional WordPress development, custom plugins, and web solutions for businesses in {STATE}. White-label services for agencies in {CITY_1}, {CITY_2}, {CITY_3}."
- **Focus Keyword**: "WordPress development {STATE}"

### URL Structure
Clean URLs without post type slug:
- Format: `/wordpress-development-services-{state}/`
- Example: `/wordpress-development-services-california/`

## API Configuration

### Current Model Settings
```php
'model' => 'claude-sonnet-4-20250514'
'max_tokens' => 4000
'temperature' => 0.7  // Balanced creativity and consistency
'anthropic-version' => '2023-06-01'
```

### Rate Limiting
- **Delay Between Requests**: 1 second minimum
- **Timeout**: 60 seconds per request
- **Progress Tracking**: Real-time duration monitoring
- **Error Handling**: 3 retry attempts with exponential backoff

## Content Quality Assurance

### Automated Checks
- WordPress block syntax validation
- Keyword density monitoring
- Character count verification (480-640 words)
- CTA placement verification

### Manual Review Points
1. **Geographic Relevance**: Natural mention of state and cities
2. **Natural Keyword Integration**: No keyword stuffing
3. **Service Focus**: Technical capabilities without industry claims
4. **Local Authenticity**: City names feel natural
5. **Professional Tone**: Factual without exaggeration
6. **Clear CTAs**: Multiple conversion opportunities
7. **Block Structure**: Proper Gutenberg formatting

## Performance Monitoring

### Content Metrics
- Organic search rankings for target keywords
- Page engagement metrics (time on page, bounce rate)
- Conversion rates from CTAs
- Internal link click-through rates

### Technical Metrics
- API response times (tracked with duration display)
- Content generation success rates
- WordPress block parsing accuracy
- SEO meta data completeness
- XML sitemap generation and validation

## Troubleshooting

### Common Content Issues

#### Block Syntax Errors
**Problem**: Malformed WordPress blocks
**Solution**: Verify exact block markup in prompt template

#### Missing CTAs
**Problem**: CTA blocks not appearing before H2s
**Solution**: Check H2 detection and CTA insertion logic

#### Generic Content
**Problem**: Similar content across states
**Solution**: Strengthen geographic relevance and city mentions

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
- **Does not require Claude API key**: Uses only existing local page data
- **No API calls**: Content is generated programmatically using WordPress block syntax
- **Static content**: Uses predefined template with dynamic state list from WP_Query
- **Page details**: Creates/updates `wordpress-development-services-usa` page

### Sitemap Generation
The `--generate-sitemap` command creates XML sitemaps. This command:
- **Does not require Claude API key**: Uses only existing local page data
- **No API calls**: Generates XML using WordPress permalink data
- **Static output**: Creates `sitemap-local.xml` in WordPress root directory

## Version History

### v1.0.0 (January 2025)
- Updated to Claude Sonnet 4 model
- Implemented WordPress block editor format
- Added systematic CTA placement before H2 headings
- Reduced content length to 480-640 words (20% reduction)
- Emphasized remote-first messaging
- **Removed industry verbosity and specialization**
- **Eliminated state-specific industry angles**
- **Simplified to geographic relevance only**
- Added progress indicators and duration tracking
- Implemented clean URL structure without post type slug
- Fixed comma-delimited multiple state handling
- Added XML sitemap generation functionality
- **Implemented secure API key storage using AES-256-CBC encryption**
- **Uses WordPress salts (AUTH_KEY, SECURE_AUTH_KEY, etc.) for encryption key derivation**
- **Only encrypted data stored in database - no plaintext keys**

---

**Last Updated**: January 2025  
**Claude Model**: claude-sonnet-4-20250514  
**Content Format**: WordPress Block Editor (Gutenberg)  
**API Version**: 2023-06-01  
**Content Strategy**: Generic services with geographic relevance
