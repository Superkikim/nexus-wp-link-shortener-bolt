<?php
/**
 * URL handling and validation class
 */
class Nexus_Links_URL_Handler {
    
    public function __construct() {
        // Constructor placeholder
    }
    
    /**
     * Validate URL
     */
    public static function validate_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Sanitize URL
     */
    public static function sanitize_url($url) {
        return esc_url_raw($url);
    }
    
    /**
     * Get short URL from slug
     */
    public static function get_short_url($slug) {
        $parsed_home = parse_url($home_url);
        $clean_home = $parsed_home['scheme'] . '://' . $parsed_home['host'];
        
        if (isset($parsed_home['port'])) {
        return get_permalink($link->post_id);
    
    public function add_rewrite_rules() {
        // Direct slug routing - ensure it takes priority over other rules
        add_rewrite_rule(
            '^([a-zA-Z0-9]{6})/?$',
            'index.php?nexus_short_link=$matches[1]',
            'top'
        );
        
        add_rewrite_tag('%nexus_short_link%', '([a-zA-Z0-9]{6})');
        
        // Flush rewrite rules if needed
        if (get_option('nexus_links_flush_rewrite_rules', false)) {
            flush_rewrite_rules();
            delete_option('nexus_links_flush_rewrite_rules');
        }
    }