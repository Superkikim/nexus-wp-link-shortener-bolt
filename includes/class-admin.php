<?php
/**
 * Admin interface class
 */
class Nexus_Links_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_nexus_create_instant_link', array($this, 'ajax_create_instant_link'));
        add_action('wp_ajax_nexus_copy_to_clipboard', array($this, 'ajax_copy_to_clipboard'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        $capability = 'edit_posts';
        
        add_menu_page(
            __('Link Shortener', 'nexus-wp-link-shortener'),
            __('Short Links', 'nexus-wp-link-shortener'),
            $capability,
            'nexus-links',
            array($this, 'dashboard_page'),
            'dashicons-admin-links',
            30
        );
        
        add_submenu_page(
            'nexus-links',
            __('Dashboard', 'nexus-wp-link-shortener'),
            __('Dashboard', 'nexus-wp-link-shortener'),
            $capability,
            'nexus-links',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'nexus-links',
            __('Pages', 'nexus-wp-link-shortener'),
            __('Pages', 'nexus-wp-link-shortener'),
            $capability,
            'nexus-links-pages',
            array($this, 'pages_tab')
        );
        
        add_submenu_page(
            'nexus-links',
            __('Posts', 'nexus-wp-link-shortener'),
            __('Posts', 'nexus-wp-link-shortener'),
            $capability,
            'nexus-links-posts',
            array($this, 'posts_tab')
        );
        
        // Add CPT tabs dynamically
        $cpts = $this->get_supported_post_types();
        foreach ($cpts as $cpt) {
            $post_type_object = get_post_type_object($cpt);
            if ($post_type_object) {
                add_submenu_page(
                    'nexus-links',
                    $post_type_object->labels->name,
                    $post_type_object->labels->name,
                    $capability,
                    'nexus-links-' . $cpt,
                    array($this, 'cpt_tab')
                );
            }
        }
        
        add_submenu_page(
            'nexus-links',
            __('Settings', 'nexus-wp-link-shortener'),
            __('Settings', 'nexus-wp-link-shortener'),
            'manage_options',
            'nexus-links-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Get supported custom post types
     */
    private function get_supported_post_types() {
        $cpts = get_post_types(array(
            'public' => true,
            'show_ui' => true,
            '_builtin' => false
        ), 'names');
        
        return array_filter($cpts, function($cpt) {
            return $cpt !== 'attachment';
        });
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'nexus-links') === false) {
            return;
        }
        
        wp_enqueue_script(
            'nexus-links-admin',
            NEXUS_LINKS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            NEXUS_LINKS_VERSION,
            true
        );
        
        wp_enqueue_style(
            'nexus-links-admin',
            NEXUS_LINKS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            NEXUS_LINKS_VERSION
        );
        
        wp_localize_script('nexus-links-admin', 'nexusLinks', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nexus_links_nonce'),
            'strings' => array(
                'copied' => __('Copied to clipboard!', 'nexus-wp-link-shortener'),
                'error' => __('Error occurred', 'nexus-wp-link-shortener'),
                'creating' => __('Creating...', 'nexus-wp-link-shortener'),
                'created' => __('Created!', 'nexus-wp-link-shortener')
            )
        ));
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        require_once NEXUS_LINKS_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }
    
    /**
     * Pages tab
     */
    public function pages_tab() {
        $this->content_tab('page');
    }
    
    /**
     * Posts tab
     */
    public function posts_tab() {
        $this->content_tab('post');
    }
    
    /**
     * Custom post type tab
     */
    public function cpt_tab() {
        $screen = get_current_screen();
        $post_type = str_replace('short-links_page_nexus-links-', '', $screen->id);
        $this->content_tab($post_type);
    }
    
    /**
     * Generic content tab
     */
    private function content_tab($post_type) {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $post_type_object = get_post_type_object($post_type);
        $posts = $this->get_posts_with_links($post_type);
        
        require_once NEXUS_LINKS_PLUGIN_DIR . 'templates/admin-content-tab.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        require_once NEXUS_LINKS_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    /**
     * Get posts with link information
     */
    private function get_posts_with_links($post_type) {
        global $wpdb;
        
        $posts_table = $wpdb->posts;
        $links_table = $wpdb->prefix . 'nexus_links';
        
        $posts = $wpdb->get_results($wpdb->prepare("
            SELECT p.*, 
                   COUNT(l.id) as link_count
            FROM $posts_table p
            LEFT JOIN $links_table l ON p.ID = l.post_id AND l.post_type = %s
            WHERE p.post_type = %s 
                AND p.post_status = 'publish'
            GROUP BY p.ID
            ORDER BY p.post_date DESC
        ", $post_type, $post_type));
        
        return $posts;
    }
    
    /**
     * AJAX handler for instant link creation
     */
    public function ajax_create_instant_link() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexus_links_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }
        
        $post_id = intval($_POST['post_id']);
        $post_type = sanitize_text_field($_POST['post_type']);
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Post not found');
        }
        
        $link_data = array(
            'post_id' => $post_id,
            'post_type' => $post_type,
            'name' => $post->post_title,
            'http_status' => 302,
            'active' => 1
        );
        
        $result = Nexus_Links_Database::create_link($link_data);
        
        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error('Failed to create link');
        }
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        if (!wp_verify_nonce($_POST['nexus_links_settings_nonce'], 'nexus_links_save_settings')) {
            return;
        }
        
        $settings = array(
            'retention_period' => intval($_POST['retention_period']),
            'forward_utm_params' => isset($_POST['forward_utm_params']),
            'analytics_enabled' => isset($_POST['analytics_enabled']),
            'bot_detection_enabled' => isset($_POST['bot_detection_enabled']),
            'allowed_roles' => isset($_POST['allowed_roles']) ? $_POST['allowed_roles'] : array()
        );
        
        foreach ($settings as $key => $value) {
            update_option('nexus_links_' . $key, $value);
        }
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'nexus-wp-link-shortener') . '</p></div>';
        });
    }
}