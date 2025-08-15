<?php
/**
 * Frontend functionality class
 */
class Nexus_Links_Frontend {
    
    public function __construct() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_short_url'));
    }
    
    /**
     * Add rewrite rules for short URLs
     */
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
    
    /**
     * Handle short URL redirects
     */
    public function handle_short_url() {
        $slug = get_query_var('nexus_short_link');
        
        if (!$slug) {
            return;
        }
        
        // Get link from database
        $link = Nexus_Links_Database::get_link_by_slug($slug);
        
        if (!$link) {
            status_header(404);
            wp_redirect(home_url(), 301);
            exit;
        }
        
        // Check if link is active
        if (!$link->active) {
            status_header(404);
            wp_redirect(home_url(), 301);
            exit;
        }
        
        // Record click analytics (async)
        $this->record_click($link);
        
        // Determine target URL
        $target_url = $this->get_target_url($link);
        
        // Clean the target URL to remove any unwanted parameters
        $target_url = $this->clean_target_url($target_url);
        
        // Perform redirect
        wp_redirect($target_url, $link->http_status);
        exit;
    }
    
    /**
     * Get target URL for the link
     */
    private function get_target_url($link) {
        // Use custom target URL if set
        if (!empty($link->target_url)) {
            return $link->target_url;
        }
        
        // Otherwise use post permalink
        return get_permalink($link->post_id);
    }
    
    /**
     * Clean target URL to remove unwanted parameters
     */
    private function clean_target_url($url) {
        // Parse the URL
        $parsed_url = parse_url($url);
        
        if (!$parsed_url) {
            return $url;
        }
        
        // If there are query parameters, filter out unwanted ones
        if (isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $query_params);
            
            // Remove lang parameter if it's the default 'en'
            if (isset($query_params['lang']) && $query_params['lang'] === 'en') {
                unset($query_params['lang']);
            }
            
            // Rebuild the URL
            $clean_query = http_build_query($query_params);
            $parsed_url['query'] = $clean_query;
            
            // If no query parameters left, remove the query component
            if (empty($clean_query)) {
                unset($parsed_url['query']);
            }
        }
        
        // Rebuild the URL
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        
        return $scheme . $host . $port . $path . $query . $fragment;
    }
    
    /**
     * Record click analytics
     */
    private function record_click($link) {
        if (!get_option('nexus_links_analytics_enabled', true)) {
            return;
        }
        
        $analytics = new Nexus_Links_Analytics();
        $analytics->record_click($link->id);
    }
}