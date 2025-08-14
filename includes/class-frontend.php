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
        add_rewrite_rule(
            '^go/([a-zA-Z0-9]{6})/?$',
            'index.php?nexus_short_link=$matches[1]',
            'top'
        );
        
        add_rewrite_tag('%nexus_short_link%', '([a-zA-Z0-9]{6})');
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
            wp_redirect(home_url(), 404);
            exit;
        }
        
        // Record click analytics (async)
        $this->record_click($link);
        
        // Determine target URL
        $target_url = $this->get_target_url($link);
        
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