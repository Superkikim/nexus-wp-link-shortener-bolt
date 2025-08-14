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
        return home_url('/go/' . $slug);
    }
    
    /**
     * Extract domain from URL
     */
    public static function extract_domain($url) {
        $parsed = parse_url($url);
        return isset($parsed['host']) ? $parsed['host'] : '';
    }
}