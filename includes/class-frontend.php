<?php
/**
 * Frontend functionality class
 */
class Nexus_Links_Frontend {
    
    public function __construct() {
        // Add rewrite rules with highest priority to override translator plugin
        add_action('init', array($this, 'add_rewrite_rules'), 0);
        add_action('template_redirect', array($this, 'handle_short_url'));

        // Debug: Log environment info
        add_action('init', array($this, 'debug_environment'), 999);

        // Force rewrite rules refresh on plugin activation
        register_activation_hook(NEXUS_LINKS_PLUGIN_FILE, array($this, 'force_rewrite_flush'));

        // Hook after other plugins to ensure our rules are preserved
        add_action('wp_loaded', array($this, 'ensure_rewrite_rules'), 999);
    }
    
    /**
     * Add rewrite rules for short URLs
     */
    public function add_rewrite_rules() {
        error_log('[Nexus Debug] add_rewrite_rules() called');
        error_log('[Nexus Debug] Home URL: ' . home_url());
        error_log('[Nexus Debug] Site URL: ' . site_url());

        // Add rewrite tag first
        add_rewrite_tag('%nexus_short_link%', '([a-zA-Z0-9]{6})');

        // Direct slug routing - ensure it takes priority over other rules
        add_rewrite_rule(
            '^([a-zA-Z0-9]{6})/?$',
            'index.php?nexus_short_link=$matches[1]',
            'top'
        );

        error_log('[Nexus Debug] Rewrite rules added');

        // Force flush rewrite rules to ensure our rule is applied
        $rules_version = get_option('nexus_links_rules_version', '0');
        $current_version = '1.0.2'; // Increment this when rules change

        if ($rules_version !== $current_version) {
            error_log('[Nexus Debug] Rules version mismatch, forcing flush');

            // Delete existing rules to force complete rebuild
            delete_option('rewrite_rules');
            flush_rewrite_rules();
            update_option('nexus_links_rules_version', $current_version);
        }

        // Also flush if the option is set
        if (get_option('nexus_links_flush_rewrite_rules', false)) {
            error_log('[Nexus Debug] Manual flush requested');
            flush_rewrite_rules();
            delete_option('nexus_links_flush_rewrite_rules');
        }

        // Debug: Check if our rule exists
        global $wp_rewrite;
        $rules = $wp_rewrite->wp_rewrite_rules();
        $our_rule_exists = isset($rules['^([a-zA-Z0-9]{6})/?$']);
        error_log('[Nexus Debug] Our rewrite rule exists: ' . ($our_rule_exists ? 'YES' : 'NO'));

        if (!$our_rule_exists) {
            error_log('[Nexus Debug] Our rule missing! Forcing immediate flush...');
            flush_rewrite_rules();

            // Check again after flush
            $rules = $wp_rewrite->wp_rewrite_rules();
            $our_rule_exists = isset($rules['^([a-zA-Z0-9]{6})/?$']);
            error_log('[Nexus Debug] Our rule exists after flush: ' . ($our_rule_exists ? 'YES' : 'NO'));
        }
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

        // Debug: Log all available query vars
        global $wp_query;
        if (isset($wp_query->query_vars)) {
            error_log('[Nexus Debug] All query vars: ' . var_export($wp_query->query_vars, true));
        }

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

    /**
     * Force rewrite rules flush
     */
    public function force_rewrite_flush() {
        error_log('[Nexus Debug] force_rewrite_flush() called');
        update_option('nexus_links_flush_rewrite_rules', true);
        flush_rewrite_rules();
    }

    /**
     * Ensure our rewrite rules are preserved after other plugins load
     */
    public function ensure_rewrite_rules() {
        global $wp_rewrite;

        error_log('[Nexus Debug] ensure_rewrite_rules() called after wp_loaded');

        // Check if our rule still exists after all plugins have loaded
        $rules = $wp_rewrite->wp_rewrite_rules();
        $our_rule_exists = isset($rules['^([a-zA-Z0-9]{6})/?$']);

        error_log('[Nexus Debug] Our rule exists after wp_loaded: ' . ($our_rule_exists ? 'YES' : 'NO'));

        if (!$our_rule_exists) {
            error_log('[Nexus Debug] Our rule was overridden! Re-adding with highest priority...');

            // Re-add our rule with highest priority
            add_rewrite_tag('%nexus_short_link%', '([a-zA-Z0-9]{6})');
            add_rewrite_rule(
                '^([a-zA-Z0-9]{6})/?$',
                'index.php?nexus_short_link=$matches[1]',
                'top'
            );

            // Force immediate flush
            flush_rewrite_rules();

            error_log('[Nexus Debug] Rules re-added and flushed');
        }

        // Also check for conflicts with translator plugin
        $this->check_translator_conflicts($rules);
    }

    /**
     * Check for conflicts with translator plugin and resolve them
     */
    private function check_translator_conflicts($rules) {
        error_log('[Nexus Debug] Checking for translator conflicts...');

        // Look for translator rules that might interfere
        $translator_rules = array();
        foreach ($rules as $pattern => $replacement) {
            if (strpos($replacement, 'nexus_ai_wp_lang') !== false) {
                $translator_rules[$pattern] = $replacement;
            }
        }

        if (!empty($translator_rules)) {
            error_log('[Nexus Debug] Found translator rules: ' . var_export($translator_rules, true));

            // Ensure our 6-character rule comes before any translator rules
            global $wp_rewrite;
            $current_rules = $wp_rewrite->wp_rewrite_rules();

            // If our rule is not first, we need to reorder
            $rule_keys = array_keys($current_rules);
            $our_rule_position = array_search('^([a-zA-Z0-9]{6})/?$', $rule_keys);

            if ($our_rule_position !== 0) {
                error_log('[Nexus Debug] Our rule is not first (position: ' . $our_rule_position . '), forcing reorder...');

                // Force a complete rewrite rules rebuild
                delete_option('rewrite_rules');
                flush_rewrite_rules();

                error_log('[Nexus Debug] Forced complete rewrite rules rebuild');
            }
        }
    }
}