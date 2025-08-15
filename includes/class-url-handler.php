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
        // Get clean home URL without any parameters
        $home_url = home_url();
        
        // Remove any existing query parameters from home URL
        $parsed_home = parse_url($home_url);
        $clean_home = $parsed_home['scheme'] . '://' . $parsed_home['host'];
        
        if (isset($parsed_home['port'])) {
            $clean_home .= ':' . $parsed_home['port'];
        }
        
        if (isset($parsed_home['path'])) {
            $clean_home .= $parsed_home['path'];
        }
        
        // Ensure no trailing slash conflicts
        $clean_home = rtrim($clean_home, '/');
        
        return $clean_home . '/' . $slug;
    }
    
    /**
     * Extract domain from URL
     */
    public static function extract_domain($url) {
        $parsed = parse_url($url);
        return isset($parsed['host']) ? $parsed['host'] : '';
    }
}