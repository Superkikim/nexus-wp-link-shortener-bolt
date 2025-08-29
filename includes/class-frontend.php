<?php
/**
 * Frontend functionality class
 */
class Nexus_Links_Frontend {
    
    public function __construct() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_short_url'));

        // Debug: Log environment info
        add_action('init', array($this, 'debug_environment'), 999);
    }
    
    /**
     * Add rewrite rules for short URLs
     */
    public function add_rewrite_rules() {
        error_log('[Nexus Debug] add_rewrite_rules() called');
        error_log('[Nexus Debug] Home URL: ' . home_url());
        error_log('[Nexus Debug] Site URL: ' . site_url());

        // Direct slug routing - ensure it takes priority over other rules
        add_rewrite_rule(
            '^([a-zA-Z0-9]{6})/?$',
            'index.php?nexus_short_link=$matches[1]',
            'top'
        );

        add_rewrite_tag('%nexus_short_link%', '([a-zA-Z0-9]{6})');

        error_log('[Nexus Debug] Rewrite rules added');

        // Flush rewrite rules if needed
        if (get_option('nexus_links_flush_rewrite_rules', false)) {
            error_log('[Nexus Debug] Flushing rewrite rules');
            flush_rewrite_rules();
            delete_option('nexus_links_flush_rewrite_rules');
        }

        // Debug: Show current rewrite rules
        global $wp_rewrite;
        error_log('[Nexus Debug] Current rewrite rules: ' . var_export($wp_rewrite->wp_rewrite_rules(), true));
    }
    
    /**
     * Handle short URL redirects
     */
    public function handle_short_url() {
        $slug = get_query_var('nexus_short_link');

        // Debug: Log the initial request
        error_log('[Nexus Debug] handle_short_url() called');
        error_log('[Nexus Debug] Request URI: ' . $_SERVER['REQUEST_URI']);
        error_log('[Nexus Debug] Query var nexus_short_link: ' . var_export($slug, true));
        error_log('[Nexus Debug] All query vars: ' . var_export(get_query_var(), true));

        if (!$slug) {
            error_log('[Nexus Debug] No slug found, returning early');
            return;
        }

        error_log('[Nexus Debug] Processing slug: ' . $slug);

        // Get link from database
        $link = Nexus_Links_Database::get_link_by_slug($slug);

        error_log('[Nexus Debug] Database lookup result: ' . var_export($link, true));

        if (!$link) {
            error_log('[Nexus Debug] Link not found in database, redirecting to home');
            status_header(404);
            wp_redirect(home_url(), 301);
            exit;
        }

        // Check if link is active
        if (!$link->active) {
            error_log('[Nexus Debug] Link is inactive, redirecting to home');
            status_header(404);
            wp_redirect(home_url(), 301);
            exit;
        }

        error_log('[Nexus Debug] Link is active, proceeding with redirect');

        // Record click analytics (async)
        $this->record_click($link);
        error_log('[Nexus Debug] Analytics recorded');

        // Determine target URL
        $target_url = $this->get_target_url($link);
        error_log('[Nexus Debug] Target URL determined: ' . $target_url);
        error_log('[Nexus Debug] HTTP status: ' . $link->http_status);

        // Perform redirect
        error_log('[Nexus Debug] Performing redirect to: ' . $target_url);
        wp_redirect($target_url, $link->http_status);
        exit;
    }
    
    /**
     * Get target URL for the link
     */
    private function get_target_url($link) {
        error_log('[Nexus Debug] get_target_url() called');
        error_log('[Nexus Debug] Link data: ' . var_export($link, true));

        // Use custom target URL if set
        if (!empty($link->target_url)) {
            error_log('[Nexus Debug] Using custom target URL: ' . $link->target_url);
            return $link->target_url;
        }

        // Otherwise use post permalink
        $permalink = get_permalink($link->post_id);
        error_log('[Nexus Debug] Using post permalink for post_id ' . $link->post_id . ': ' . $permalink);

        return $permalink;
    }
    

    
    /**
     * Record click analytics
     */
    private function record_click($link) {
        error_log('[Nexus Debug] record_click() called for link ID: ' . $link->id);

        $analytics_enabled = get_option('nexus_links_analytics_enabled', true);
        error_log('[Nexus Debug] Analytics enabled: ' . var_export($analytics_enabled, true));

        if (!$analytics_enabled) {
            error_log('[Nexus Debug] Analytics disabled, skipping recording');
            return;
        }

        try {
            $analytics = new Nexus_Links_Analytics();
            $analytics->record_click($link->id);
            error_log('[Nexus Debug] Analytics recorded successfully');
        } catch (Exception $e) {
            error_log('[Nexus Debug] Analytics recording failed: ' . $e->getMessage());
        }
    }

    /**
     * Debug environment information
     */
    public function debug_environment() {
        // Only log once per request
        static $logged = false;
        if ($logged) return;
        $logged = true;

        error_log('[Nexus Debug] === ENVIRONMENT INFO ===');
        error_log('[Nexus Debug] WordPress Version: ' . get_bloginfo('version'));
        error_log('[Nexus Debug] PHP Version: ' . PHP_VERSION);
        error_log('[Nexus Debug] Home URL: ' . home_url());
        error_log('[Nexus Debug] Site URL: ' . site_url());
        error_log('[Nexus Debug] Permalink structure: ' . get_option('permalink_structure'));
        error_log('[Nexus Debug] Is multisite: ' . (is_multisite() ? 'Yes' : 'No'));
        error_log('[Nexus Debug] Active theme: ' . get_template());
        error_log('[Nexus Debug] Request URI: ' . $_SERVER['REQUEST_URI']);
        error_log('[Nexus Debug] HTTP Host: ' . $_SERVER['HTTP_HOST']);
        error_log('[Nexus Debug] Server software: ' . $_SERVER['SERVER_SOFTWARE']);

        // Check if rewrite rules are working
        global $wp_rewrite;
        error_log('[Nexus Debug] Rewrite rules enabled: ' . ($wp_rewrite->using_permalinks() ? 'Yes' : 'No'));
        error_log('[Nexus Debug] Rewrite base: ' . $wp_rewrite->root);

        // Check database connection
        global $wpdb;
        $table = $wpdb->prefix . 'nexus_links';
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        error_log('[Nexus Debug] Total links in database: ' . $count);

        error_log('[Nexus Debug] === END ENVIRONMENT INFO ===');
    }
}