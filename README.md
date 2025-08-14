# Nexus WP Link Shortener

A 100% internal WordPress link shortener plugin with comprehensive analytics and no external dependencies.

## Description

Nexus WP Link Shortener provides a complete link shortening solution built entirely within WordPress. Create short, trackable links for your content with detailed analytics, all without relying on external services.

## Features

### Core Functionality
- **Internal Link Shortening**: Create short URLs with format `/go/{slug}` (6 random characters)
- **Multiple Links per Content**: Support for multiple short links per post, page, or custom post type
- **Flexible Redirects**: Choose between 301 (permanent) and 302 (temporary) redirects
- **Clean URLs**: No tracking parameters by default (configurable)

### Analytics & Tracking
- **Comprehensive Analytics**: Track clicks, referrers, devices, browsers, operating systems
- **Bot Detection**: Automatic bot filtering with configurable detection
- **GDPR Compliant**: IP anonymization and configurable data retention (13-month default)
- **UTM Parameter Support**: Optional UTM parameter forwarding and tracking

### Admin Interface
- **Dashboard**: Visual analytics with charts and statistics
- **Content Tabs**: Separate views for Pages, Posts, and Custom Post Types
- **Instant Actions**: One-click link creation with copy-to-clipboard
- **Bulk Management**: Manage all short links for specific content items

### Security & Performance
- **Role-Based Permissions**: Configurable user role access (Administrators and Editors by default)
- **Database Optimization**: Proper indexing for performance
- **Collision Avoidance**: Unique slug generation with fallback handling
- **Safe CPT Detection**: Only detects public custom post types with UI

## Installation

1. Upload the plugin files to `/wp-content/plugins/nexus-wp-link-shortener/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to 'Short Links' in the admin menu to start creating links

## Usage

### Creating Short Links

1. **Instant Creation**: Use the "Instant Create + Copy" button on any content item
2. **Custom Links**: Create links with custom names, campaigns, and descriptions
3. **Target Override**: Optionally redirect to custom URLs instead of the original content

### Managing Links

- View all links for a specific post or page
- Edit link names, campaigns, and descriptions
- Toggle active/paused status
- Change redirect type (301/302)
- Delete unused links

### Analytics

- View click statistics and trends
- Analyze referrer sources
- Monitor bot vs. human traffic
- Track campaign performance
- Export data for external analysis

### Settings

- Configure data retention period
- Enable/disable UTM parameter forwarding
- Set user role permissions
- Control analytics and bot detection

## Database Schema

### Links Table (`wp_nexus_links`)
- `id`: Primary key
- `post_id`: Associated WordPress post ID
- `post_type`: Content type (post, page, custom)
- `slug`: 6-character unique identifier
- `name`: Human-readable link name
- `campaign`: Optional campaign identifier
- `description`: Optional description
- `target_url`: Optional custom redirect URL
- `http_status`: HTTP status code (301/302)
- `active`: Active/paused status
- `created_by`: Creator user ID
- `created_at`: Creation timestamp

### Analytics Table (`wp_nexus_clicks`)
- `id`: Primary key
- `link_id`: Foreign key to links table
- `timestamp`: Click timestamp
- `referrer`: HTTP referrer
- `user_agent`: Browser user agent
- `device`: Device type (Desktop/Mobile/Tablet)
- `browser`: Browser name
- `os`: Operating system
- `anonymized_ip`: GDPR-compliant IP address
- `country`/`region`: Geographic data (optional)
- `is_bot`: Bot detection flag
- `utm_*`: UTM parameter values
- `query_params_json`: Additional query parameters

## Privacy & GDPR Compliance

- **IP Anonymization**: IP addresses are automatically anonymized
- **Data Retention**: Configurable retention period with automatic cleanup
- **Consent Friendly**: No tracking cookies or external requests
- **Transparent**: Clear labeling of privacy implications in settings

## Technical Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher (or equivalent MariaDB)

## REST API

The plugin provides REST API endpoints for integration:

- `GET /wp-json/nexus-links/v1/links` - Get links
- `POST /wp-json/nexus-links/v1/links` - Create link
- `PUT /wp-json/nexus-links/v1/links/{id}` - Update link
- `DELETE /wp-json/nexus-links/v1/links/{id}` - Delete link
- `GET /wp-json/nexus-links/v1/analytics` - Get analytics data

## Contributing

This plugin follows WordPress coding standards and best practices. Contributions are welcome via GitHub.

## Changelog

### 1.0.0
- Initial release
- Core link shortening functionality
- Analytics and tracking
- Admin interface
- GDPR compliance features

## Author

**Superkikim**  
Email: superkikim@sissaoui.com  
GitHub: https://github.com/Superkikim/nexus-wp-link-shortener

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, feature requests, or bug reports, please visit the GitHub repository or contact the author directly.