# Disclaimer

This plugin was built specifically for the 84em.com website and its unique functionalities.

It is not intended for use on any other website.

If you chose to use & install it, you do so at your own risk.

**Want a version that you can run on your own site? [Contact 84EM](https://84em.com/contact/)**.

# 84EM Local Pages Generator Plugin

A WordPress plugin that automatically generates SEO-optimized Local Pages for each US state using Claude AI and WP-CLI, designed specifically for 84em.com.

## Overview

This plugin creates unique, locally-focused landing pages for WordPress development services in all 50 US states. Each page targets state-specific keywords while incorporating the 6 largest cities and geographic relevance to avoid duplicate content penalties.

## Features

- **Custom Post Type**: Creates "Local Pages" with clean URLs (no /local/ slug)
- **WP-CLI Integration**: Complete command-line management interface with progress bars
- **Claude AI Content**: Generates unique 300-400 word pages per state using Claude Sonnet 4
- **SEO Optimization**: Built-in SEO meta data and structured content
- **Geographic Relevance**: Each state focuses on local cities and geographic relevance only
- **Bulk Operations**: Create, update, or delete multiple pages efficiently
- **State-Based Commands**: Generate/update by state name instead of post IDs
- **Call-to-Action Integration**: Automatic CTA placement with contact links
- **WordPress Block Editor**: Content generated in Gutenberg block format
- **Rate Limiting**: Respects API limits with configurable delays and duration tracking
- **Progress Indicators**: Real-time feedback on API requests and processing
- **XML Sitemap Generation**: Generate XML sitemaps for local pages with WP-CLI
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

### Step 2: Generate All State Pages
```bash
wp 84em local-pages --state=all
```

### Step 3: Generate Index Page
```bash
wp 84em local-pages --generate-index
```

### Step 4: Verify Results
```bash
# Check created pages
wp post list --post_type=local --format=table

# Check index page
wp post list --post_type=page --name=wordpress-development-services-usa --format=table
```

## Command Reference

### API Key Management

**Set Claude API Key:**
```bash
wp 84em local-pages --set-api-key
# Interactive prompt - paste your key securely without shell history
```

### Page Generation

**Generate All 50 State Pages:**
```bash
wp 84em local-pages --state=all
```

**Generate Specific States:**
```bash
# Single state
wp 84em local-pages --state="California"

# Multiple states
wp 84em local-pages --state="California,New York,Texas"
```

### Update Operations

**Update All Existing Pages:**
```bash
wp 84em local-pages --update --state=all
```

**Update Specific States:**
```bash
# Single state
wp 84em local-pages --update --state="California"

# Multiple states
wp 84em local-pages --update --state="California,New York,Texas"
```

### Delete Operations

**Delete All Local Pages:**
```bash
wp 84em local-pages --delete --state=all
```

**Delete Specific States:**
```bash
# Single state
wp 84em local-pages --delete --state="California"

# Multiple states
wp 84em local-pages --delete --state="California,New York,Texas"
```

### Help and Information

**Show Available Commands:**
```bash
wp 84em local-pages
```

### Sitemap Generation

**Generate XML Sitemap:**
```bash
wp 84em local-pages --generate-sitemap
```

### Index Page Generation

**Generate Index Page with Alphabetized State List:**
```bash
wp 84em local-pages --generate-index
```

## How It Works

### Content Generation Process

1. **State Analysis**: Plugin identifies the state and its 6 largest cities
2. **Geographic Focus**: Uses state and major cities for local relevance
3. **Claude Prompt**: Sends structured prompt to Claude AI API with remote-first emphasis
4. **Content Creation**: Generates 300-400 words of unique content in WordPress block format
5. **CTA Integration**: Adds call-to-action blocks before each H2 heading (except first)
6. **SEO Integration**: Adds optimized titles, meta descriptions, and LD-JSON Schema data
7. **Post Creation**: Saves as "local" custom post type with clean URLs

### Content Strategy

Each state receives unique content through:

- **Geographic Relevance**: State and major city mentions for local SEO
- **Service Focus**: WordPress development capabilities and technical expertise  
- **Remote Emphasis**: 100% remote operations and nationwide service
- **Clear CTAs**: Multiple conversion opportunities throughout content

*No industry-specific angles - generic services applicable to all business types*

### SEO Implementation

Each generated page includes:

- **SEO Title**: "Expert WordPress Development Services in [State] | 84EM"
- **Meta Description**: State and city-specific description
- **Structured Content**: H2/H3 headings with bold formatting
- **Local Keywords**: Natural integration of city and state names
- **Call-to-Actions**: Inline links and prominent CTA blocks
- **LD-JSON Schema**: For SEO purposes

### Call-to-Action Features

- **Inline CTAs**: 2-3 contextual links throughout content linking to /contact/
- **Prominent CTA Blocks**: Placed before every H2 heading (except first)
- **Styled Buttons**: "Start Your WordPress Project" with custom styling
- **Natural Integration**: CTAs flow naturally within content

## API Configuration

### Claude API Setup

1. **Create Anthropic Account**: Visit [console.anthropic.com](https://console.anthropic.com)
2. **Generate API Key**: Create new API key in dashboard
3. **Configure Billing**: Set up payment method for usage-based pricing
4. **Set Rate Limits**: Configure appropriate limits for your needs

### Cost Estimates

- **Full Generation** (50 states): $2-4 per complete run (reduced content length)
- **Individual Updates**: $0.04-0.08 per page
- **Monthly Maintenance**: $6-12 depending on update frequency

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
- **Public**: Yes
- **Archive**: Yes
- **REST API**: Enabled
- **Supports**: Title, editor, thumbnail, excerpt, custom fields

### Custom Fields Stored
- `_local_page_state`: State name (e.g., "California")
- `_local_page_cities`: Comma-separated 6 largest cities
- `_genesis_title`: SEO title
- `_genesis_description`: SEO meta description

### URL Structure
```
https://84em.com/wordpress-development-services-california/
https://84em.com/wordpress-development-services-texas/
```

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
The `generate-index` command creates or updates a master index page that serves as a navigation hub for all local pages. This page provides an alphabetized directory of all US states with direct links to their respective local pages.

### Index Page Details
- **Page Slug**: `wordpress-development-services-usa`
- **Page Title**: `WordPress Development Services in USA | 84EM`
- **Page Type**: Standard WordPress page (not custom post type)
- **URL**: `https://yourdomain.com/wordpress-development-services-usa/`

### Features
- **Automatic State Discovery**: Uses WP_Query to find all published local pages
- **Alphabetical Sorting**: States are automatically sorted A-Z for easy navigation
- **Smart Create/Update**: Detects existing page and updates content, or creates new page
- **SEO Optimized**: Includes meta description and SEO title
- **WordPress Block Format**: Content generated in Gutenberg block syntax
- **Professional Content**: Includes service overview and call-to-action

### Generated Content Structure
The index page includes:
1. **Introduction**: Overview of 84EM's nationwide WordPress development services
2. **State Directory**: Alphabetized list of all states with links to local pages
3. **Service Overview**: Key WordPress development services offered
4. **Call-to-Action**: Link to contact page for inquiries

### When to Use
- **After Creating Local Pages**: Generate index after creating multiple state pages
- **Regular Updates**: Refresh index when adding new state pages
- **SEO Strategy**: Provide internal linking hub for better site structure
- **User Navigation**: Give visitors easy access to state-specific pages

### Usage in Workflows
```bash
# Complete workflow for new setup
wp 84em local-pages --state=all              # Create all state pages
wp 84em local-pages --generate-index         # Create index page
wp 84em local-pages --generate-sitemap       # Generate XML sitemap

# Monthly maintenance
wp 84em local-pages --update --state=all     # Update existing pages
wp 84em local-pages --generate-index         # Refresh index page
```

## Workflow Examples

### Initial Setup Workflow
```bash
# 1. Set API key
wp 84em local-pages --set-api-key

# 2. Test with a few states first
wp 84em local-pages --state="California,New York,Texas"

# 3. Generate all states
wp 84em local-pages --state=all

# 4. Generate index page
wp 84em local-pages --generate-index

# 5. Monitor progress
wp post list --post_type=local --format=count

# 6. Review generated content in WordPress admin
```

### Maintenance Workflow
```bash
# Monthly content refresh
wp 84em local-pages --update --state=all

# Update specific states
wp 84em local-pages --update --state="California,New York"

# Generate sitemap
wp 84em local-pages --generate-sitemap

# Generate/update index page
wp 84em local-pages --generate-index
```

### Troubleshooting Workflow
```bash
# Check for failed pages
wp post list --post_type=local --post_status=draft

# Regenerate specific failed states
wp 84em local-pages --update --state="California,Texas"

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

**"Failed to generate content for [State]"**
- Check API key validity
- Verify internet connection
- Check Anthropic service status
- Review API usage limits

**"Invalid state name"**
- Use full state names (e.g., "California", not "CA")
- Check spelling and capitalization
- Use exact state names from the 50 US states list

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
- Individual state processing indicators
- Clear success/failure messaging with emojis

### Bulk Operation Tips

1. **Monitor Progress**: Built-in progress indicators show real-time status
2. **Batch Processing**: Process in smaller batches if timeouts occur
3. **Rate Limiting**: Plugin includes 1-second delays between API calls
4. **Memory Management**: Increase PHP memory limits for large operations

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
- Can be set via environment variables

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
# Check page count
wp post list --post_type=local --format=count

# List all local pages
wp post list --post_type=local --format=table

# Check for drafts (potential failures)
wp post list --post_type=local --post_status=draft --format=table

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
wp db import 84em-local-pages-backup-20241226.sql

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
└── cta.html                         # Call-to-action block template
└── deploy.sh                        # rsync powered deployment script
```

## License

Proprietary software developed for 84EM. All rights reserved.

---

**Plugin Version**: 1.0.0  
**WordPress Tested**: 6.8+  
**PHP Minimum**: 8.2  
**Last Updated**: July 30, 2025
