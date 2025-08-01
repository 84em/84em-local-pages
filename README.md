# Disclaimer

This plugin was built specifically for the 84em.com website and its unique functionalities.

It is not intended for use on any other website.

If you chose to use & install it, you do so at your own risk.

**Want a version that you can run on your own site? [Contact 84EM](https://84em.com/contact/)**.

# 84EM Local Pages Generator Plugin

A WordPress plugin that automatically generates SEO-optimized Local Pages for each US state and city using Claude AI and WP-CLI, designed specifically for 84em.com.

## Overview

This plugin creates unique, locally-focused landing pages for WordPress development services in all 50 US states and their major cities. Each page targets location-specific keywords while incorporating geographic relevance and automatic interlinking to avoid duplicate content penalties.

## Features

- **Hierarchical Post Type**: Creates "Local Pages" with parent-child relationships (states → cities)
- **Comprehensive Coverage**: 50 state pages + 300 city pages (6 cities per state) = 350 total pages
- **WP-CLI Integration**: Complete command-line management interface with progress bars
- **Claude AI Content**: Generates unique content using Claude Sonnet 4
- **Automatic Interlinking**: City names link to city pages, service keywords link to contact page
- **SEO Optimization**: Built-in SEO meta data and structured LD-JSON schema
- **Geographic Relevance**: Each page focuses on local cities and geographic context
- **Bulk Operations**: Create, update, or delete multiple pages efficiently
- **Call-to-Action Integration**: Automatic CTA placement with contact links
- **WordPress Block Editor**: Content generated in Gutenberg block format
- **Rate Limiting**: Respects API limits with configurable delays and duration tracking
- **Progress Indicators**: Real-time feedback on API requests and processing
- **XML Sitemap Generation**: Generate XML sitemaps for all local pages with WP-CLI
- **Index Page Generation**: Create or update a master index page with alphabetized state list

## Requirements

- WordPress 6.8 or higher
- PHP 8.2 or higher
- WP-CLI 2.0 or higher
- Claude API key from Anthropic

## Installation

1. **Upload Plugin Files**
   ```bash
   # Upload to your WordPress plugins directory
   /wp-content/plugins/84em-local-pages/
   ```

2. **Activate Plugin**
    - Go to WordPress Admin → Plugins
    - Find "84EM Local Pages Generator"
    - Click "Activate"

3. **Verify WP-CLI Access**
   ```bash
   wp --info
   ```

## Quick Start

### Step 1: Configure API Key
```bash
wp 84em local-pages --set-api-key
# You will be prompted to securely paste your API key
```

### Step 2: Generate Everything (Recommended)
```bash
wp 84em local-pages --generate-all
# Creates 50 state pages + 300 city pages = 350 total pages
```

### Step 3: Generate Supporting Pages
```bash
wp 84em local-pages --generate-index
wp 84em local-pages --generate-sitemap
```

### Step 4: Verify Results
```bash
# Check created pages
wp post list --post_type=local --format=count

# Check hierarchical structure
wp post list --post_type=local --format=table

# Check index page
wp post list --post_type=page --name=wordpress-development-services-usa --format=table
```

## Command Reference

### 🚀 Bulk Operations (Recommended)

**Generate/Create Everything:**
```bash
# Generate all states and cities (350 pages)
wp 84em local-pages --generate-all

# Generate states only (50 pages)
wp 84em local-pages --generate-all --states-only
```

**Update Existing Pages:**
```bash
# Update all existing states and cities
wp 84em local-pages --update-all

# Update existing states only
wp 84em local-pages --update-all --states-only
```

### API Key Management

**Set Claude API Key:**
```bash
wp 84em local-pages --set-api-key
# Interactive prompt - paste your key securely without shell history
```

**Validate API Key:**
```bash
wp 84em local-pages --validate-api-key
```

### State Operations

**Generate/Update States:**
```bash
# All states (legacy command)
wp 84em local-pages --state=all

# Specific states
wp 84em local-pages --state="California"
wp 84em local-pages --state="California,New York,Texas"
```

**Update Existing States:**
```bash
# All states
wp 84em local-pages --update --state=all

# Specific states
wp 84em local-pages --update --state="California,New York"
```

### City Operations

**Generate/Update Cities:**
```bash
# All cities for a state
wp 84em local-pages --state="California" --city=all

# Specific cities
wp 84em local-pages --state="California" --city="Los Angeles"
wp 84em local-pages --state="California" --city="Los Angeles,San Diego,San Francisco"
```

### Delete Operations

**Delete States:**
```bash
# All states
wp 84em local-pages --delete --state=all

# Specific states
wp 84em local-pages --delete --state="California,New York"
```

**Delete Cities:**
```bash
# All cities for a state
wp 84em local-pages --delete --state="California" --city=all

# Specific cities
wp 84em local-pages --delete --state="California" --city="Los Angeles,San Diego"
```

### Supporting Operations

**Generate Index Page:**
```bash
wp 84em local-pages --generate-index
```

**Generate XML Sitemap:**
```bash
wp 84em local-pages --generate-sitemap
```

**Show Available Commands:**
```bash
wp 84em local-pages
```

## How It Works

### Hierarchical Structure

The plugin creates a hierarchical structure:

```
State Page (Parent)
├── City 1 Page (Child)
├── City 2 Page (Child)  
├── City 3 Page (Child)
├── City 4 Page (Child)
├── City 5 Page (Child)
└── City 6 Page (Child)
```

### URL Structure
```
# State pages
https://84em.com/wordpress-development-services-california/
https://84em.com/wordpress-development-services-texas/

# City pages (child pages)
https://84em.com/wordpress-development-services-california/los-angeles/
https://84em.com/wordpress-development-services-california/san-diego/
https://84em.com/wordpress-development-services-texas/houston/
https://84em.com/wordpress-development-services-texas/dallas/
```

### Content Generation Process

1. **State Analysis**: Plugin identifies the state and its 6 largest cities
2. **Hierarchical Creation**: Creates state page first, then child city pages
3. **Claude Prompt**: Sends structured prompts to Claude AI API with location-specific context
4. **Content Creation**: Generates unique content for each location
5. **Automatic Interlinking**: Links city names to city pages, service keywords to contact page
6. **CTA Integration**: Adds call-to-action blocks before each H2 heading
7. **SEO Integration**: Adds optimized titles, meta descriptions, and LD-JSON Schema data
8. **Post Creation**: Saves as hierarchical "local" custom post type with clean URLs

### Content Strategy

**State Pages (300-400 words):**
- Geographic relevance with state and major city mentions
- Service focus on WordPress development capabilities
- City names automatically linked to their respective city pages
- Service keywords automatically linked to contact page

**City Pages (250-350 words):**
- City-specific benefits and local business context  
- Geographic references to the city and state
- Service keywords automatically linked to contact page
- Parent-child relationship with state page

### Automatic Interlinking

**State Pages:**
- ✅ City names → Link to city pages
- ✅ Service keywords → Link to https://84em.com/contact/

**City Pages:**
- ✅ Service keywords → Link to https://84em.com/contact/

### SEO Implementation

**State Pages:**
- **SEO Title**: "Expert WordPress Development Services in [State] | 84EM"
- **Meta Description**: State and city-specific description
- **LD-JSON Schema**: LocalBusiness schema with city containment

**City Pages:**
- **SEO Title**: "Expert WordPress Development Services in [City], [State] | 84EM"  
- **Meta Description**: City and state-specific description
- **LD-JSON Schema**: LocalBusiness schema with city focus

### Call-to-Action Features

- **Inline CTAs**: 2-3 contextual links throughout content linking to /contact/
- **Prominent CTA Blocks**: Placed before every H2 heading
- **Styled Buttons**: "Start Your WordPress Project" with custom styling
- **Natural Integration**: CTAs flow naturally within content

## API Configuration

### Claude API Setup

1. **Create Anthropic Account**: Visit [console.anthropic.com](https://console.anthropic.com)
2. **Generate API Key**: Create new API key in dashboard
3. **Configure Billing**: Set up payment method for usage-based pricing
4. **Set Rate Limits**: Configure appropriate limits for your needs

### Cost Estimates

- **Full Generation** (350 pages): $14-28 per complete run
- **State Pages Only** (50 pages): $2-4 per run
- **Individual Updates**: $0.04-0.08 per page
- **Monthly Maintenance**: $20-40 depending on update frequency

### API Settings Used

```php
'model' => 'claude-sonnet-4-20250514'
'max_tokens' => 4000
'timeout' => 60 seconds
'rate_limit' => 1 second delay between requests
```

## Custom Post Type Details

### Post Type Configuration
- **Name**: Local Pages
- **Slug**: local (but URLs don't include /local/)
- **Hierarchical**: Yes (supports parent-child relationships)
- **Public**: Yes
- **Archive**: Yes
- **REST API**: Enabled
- **Supports**: Title, editor, thumbnail, excerpt, custom fields

### Custom Fields Stored

**State Pages:**
- `_local_page_state`: State name (e.g., "California")
- `_local_page_cities`: Comma-separated 6 largest cities
- `_genesis_title`: SEO title
- `_genesis_description`: SEO meta description
- `schema`: LD-JSON structured data

**City Pages:**
- `_local_page_state`: State name (e.g., "California")
- `_local_page_city`: City name (e.g., "Los Angeles")
- `_genesis_title`: SEO title
- `_genesis_description`: SEO meta description
- `schema`: LD-JSON structured data

## Content Features

### WordPress Block Editor Format
- All content generated in Gutenberg block syntax
- Proper block markup for paragraphs, headings, and CTAs
- Bold headings with `<strong>` tags
- Clean, structured HTML output

### Remote-First Messaging
- Emphasizes 84EM's 100% remote operations
- No mentions of on-site visits or local offices
- Focus on technical expertise and proven remote delivery
- Factual tone without hyperbole

## Index Page Feature

### Overview
The `generate-index` command creates or updates a master index page that serves as a navigation hub for all state pages. This page provides an alphabetized directory of all US states with direct links to their respective state pages.

### Index Page Details
- **Page Slug**: `wordpress-development-services-usa`
- **Page Title**: `WordPress Development Services in USA | 84EM`
- **Page Type**: Standard WordPress page (not custom post type)
- **URL**: `https://84em.com/wordpress-development-services-usa/`

### Features
- **Automatic State Discovery**: Uses WP_Query to find all published state pages
- **Alphabetical Sorting**: States are automatically sorted A-Z for easy navigation
- **Smart Create/Update**: Detects existing page and updates content, or creates new page
- **SEO Optimized**: Includes meta description and SEO title
- **WordPress Block Format**: Content generated in Gutenberg block syntax
- **Professional Content**: Includes service overview and call-to-action

## Workflow Examples

### Complete Setup Workflow
```bash
# 1. Set API key
wp 84em local-pages --set-api-key

# 2. Generate everything (350 pages)
wp 84em local-pages --generate-all

# 3. Generate supporting pages
wp 84em local-pages --generate-index
wp 84em local-pages --generate-sitemap

# 4. Verify results
wp post list --post_type=local --format=count
```

### Monthly Maintenance Workflow
```bash
# Update all existing content
wp 84em local-pages --update-all

# Refresh supporting pages
wp 84em local-pages --generate-index
wp 84em local-pages --generate-sitemap
```

### Selective Operations
```bash
# Test with a few states first
wp 84em local-pages --state="California,New York,Texas"

# Generate cities for specific states
wp 84em local-pages --state="California" --city=all
wp 84em local-pages --state="New York" --city=all

# Update specific locations
wp 84em local-pages --update --state="California"
wp 84em local-pages --state="California" --city="Los Angeles,San Diego"
```

### Troubleshooting Workflow
```bash
# Check for failed pages
wp post list --post_type=local --post_status=draft --format=table

# Check page counts
wp post list --post_type=local --format=count

# Regenerate specific failed locations
wp 84em local-pages --update --state="California"
wp 84em local-pages --state="California" --city="Los Angeles"

# Monitor error logs
tail -f /path/to/wordpress/wp-content/debug.log
```

## Error Handling

### Common Issues and Solutions

**"Claude API key not found"**
```bash
# Solution: Set the API key
wp 84em local-pages --set-api-key
```

**"Failed to generate content for [Location]"**
- Check API key validity with `--validate-api-key`
- Verify internet connection
- Check Anthropic service status
- Review API usage limits

**"Parent state page not found"**
- Create state page first before generating city pages
- Use `--generate-all` to create everything in proper order

**"Invalid state name" or "City not found in [State]"**
- Use full state names (e.g., "California", not "CA")
- Check spelling and capitalization
- City names must match the predefined list in the plugin

**"cURL timeout errors"**
```php
// Add to wp-config.php
define('WP_HTTP_TIMEOUT', 120);
```

**"Memory limit exceeded"**
```php
// Add to wp-config.php  
define('WP_MEMORY_LIMIT', '512M');
```

### Debug Mode

Enable detailed logging:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

View plugin logs:
```bash
tail -f /wp-content/debug.log | grep "84EM"
```

## Performance Features

### Progress Tracking
- Real-time progress bars for bulk operations
- API request duration tracking
- Individual location processing indicators
- Clear success/failure messaging with emojis
- Comprehensive statistics showing created/updated counts

### Bulk Operation Tips

1. **Monitor Progress**: Built-in progress indicators show real-time status
2. **Hierarchical Processing**: States are created first, then their cities
3. **Rate Limiting**: Plugin includes 1-second delays between API calls
4. **Memory Management**: Increase PHP memory limits for large operations
5. **Error Handling**: Graceful failures with detailed logging

### Caching Considerations

The plugin works with most caching plugins, but consider:
- Clear cache after bulk updates
- Exclude Local Pages from aggressive caching
- Warm cache for new pages automatically

## Security

### API Key Storage
- Keys encrypted using AES-256-CBC encryption with WordPress salts
- Encryption key derived from WordPress AUTH_KEY, SECURE_AUTH_KEY, LOGGED_IN_KEY, and NONCE_KEY
- Only encrypted data stored in database - no plaintext API keys
- Cryptographically secure initialization vector (IV) for each encryption
- Not exposed in frontend or logs

### API Key Security
- **Interactive Entry**: API keys are entered via secure prompt, not command arguments
- **No Shell History**: Keys don't appear in bash/shell command history
- **Hidden Input**: Terminal echo is disabled during key entry for privacy
- **Format Validation**: Warns if key doesn't match expected Claude API format

### Input Validation
- All user inputs sanitized and validated
- WP-CLI commands require appropriate permissions
- Post content properly escaped before display

### Rate Limiting
- Built-in delays prevent API abuse
- Configurable timeout settings
- Graceful handling of API failures

### Monitoring Commands

```bash
# Check total page count
wp post list --post_type=local --format=count

# List all local pages with hierarchy
wp post list --post_type=local --format=table

# Check for drafts (potential failures)
wp post list --post_type=local --post_status=draft --format=table

# Count state vs city pages
wp post list --post_type=local --meta_key=_local_page_city --format=count
wp post list --post_type=local --meta_key=_local_page_state --format=count

# Export all local pages
wp export --post_type=local
```

## Backup and Recovery

### Before Major Operations
```bash
# Backup database
wp db export 84em-local-pages-backup-$(date +%Y%m%d).sql

# Export existing local pages
wp export --post_type=local --dir=/backups/local-pages/
```

### Recovery Process
```bash
# Restore from database backup
wp db import 84em-local-pages-backup-20250130.sql

# Or import specific posts
wp import /backups/local-pages/local-pages-export.xml
```

## Support

### Getting Help

1. **Plugin Issues**: 84EM offers no warranty nor provides any support for this plugin
2. **API Issues**: Check [Anthropic Status](https://status.anthropic.com)
3. **WordPress Issues**: [WordPress.org Support](https://wordpress.org/support/)
4. **WP-CLI Issues**: [WP-CLI Documentation](https://wp-cli.org/)

## File Structure

```
84em-local-pages/
├── 84em-local-pages.php             # Main plugin file
├── README.md                        # This documentation
├── Claude.md                        # Claude AI prompt templates
├── changelog.md                     # Version history and release notes
├── cta.html                         # Call-to-action block template
└── deploy.sh                        # rsync powered deployment script
```

## License

Proprietary software developed for 84EM. All rights reserved.

---

**Plugin Version**: 2.0.1  
**WordPress Tested**: 6.8+  
**PHP Minimum**: 8.2  
**Last Updated**: August 1, 2025
