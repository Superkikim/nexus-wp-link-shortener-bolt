<?php
/**
 * Uninstall script for Nexus WP Link Shortener
 * This file is called when the plugin is deleted via WordPress admin
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove database tables
global $wpdb;

$links_table = $wpdb->prefix . 'nexus_links';
$clicks_table = $wpdb->prefix . 'nexus_clicks';

// Drop tables in correct order (foreign key constraints)
$wpdb->query("DROP TABLE IF EXISTS $clicks_table");
$wpdb->query("DROP TABLE IF EXISTS $links_table");

// Remove plugin options
$options = array(
    'nexus_links_db_version',
    'nexus_links_retention_period',
    'nexus_links_forward_utm_params',
    'nexus_links_allowed_roles',
    'nexus_links_analytics_enabled',
    'nexus_links_bot_detection_enabled',
    'nexus_links_flush_rewrite_rules'
);

foreach ($options as $option) {
    delete_option($option);
}

// Clean up any scheduled events
wp_clear_scheduled_hook('nexus_links_cleanup_analytics');

// Flush rewrite rules one final time
flush_rewrite_rules();