<?php
/**
 * Admin interface class
 */
class Nexus_Links_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_nexus_create_instant_link', array($this, 'ajax_create_instant_link'));
        add_action('wp_ajax_nexus_get_analytics', array($this, 'ajax_get_analytics'));
        add_action('wp_ajax_nexus_cleanup_analytics', array($this, 'ajax_cleanup_analytics'));
        add_action('wp_ajax_nexus_export_data', array($this, 'ajax_export_data'));
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
        
        // Hidden manage links page
        add_submenu_page(
            null, // Hidden from menu
            __('Manage Links', 'nexus-wp-link-shortener'),
            __('Manage Links', 'nexus-wp-link-shortener'),
            $capability,
            'nexus-links-manage',
            array($this, 'manage_links_page')
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
    
    /**
     * Manage links page for specific post
     */
    public function manage_links_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'post';
        
        if (!$post_id) {
            wp_die(__('Invalid post ID.', 'nexus-wp-link-shortener'));
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_die(__('Post not found.', 'nexus-wp-link-shortener'));
        }
        
        // Handle form submissions
        if (isset($_POST['action'])) {
            $this->handle_manage_links_actions($post_id, $post_type);
        }
        
        $links = Nexus_Links_Database::get_links_by_post($post_id, $post_type);
        
        require_once NEXUS_LINKS_PLUGIN_DIR . 'templates/admin-manage-links.php';
    }
    
    /**
     * Handle manage links form actions
     */
    private function handle_manage_links_actions($post_id, $post_type) {
        if (!wp_verify_nonce($_POST['nexus_links_nonce'], 'nexus_links_manage')) {
            return;
        }
        
        $action = sanitize_text_field($_POST['action']);
        
        switch ($action) {
            case 'create_link':
                $this->create_new_link($post_id, $post_type);
                break;
            case 'update_link':
                $this->update_existing_link();
                break;
            case 'delete_link':
                $this->delete_existing_link();
                break;
        }
    }
    
    /**
     * Create new link from manage page
     */
    private function create_new_link($post_id, $post_type) {
        $link_data = array(
            'post_id' => $post_id,
            'post_type' => $post_type,
            'name' => sanitize_text_field($_POST['name']),
            'campaign' => sanitize_text_field($_POST['campaign']),
            'description' => sanitize_textarea_field($_POST['description']),
            'target_url' => esc_url_raw($_POST['target_url']),
            'http_status' => intval($_POST['http_status']),
            'active' => isset($_POST['active']) ? 1 : 0
        );
        
        $result = Nexus_Links_Database::create_link($link_data);
        
        if ($result) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Link created successfully!', 'nexus-wp-link-shortener') . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Failed to create link.', 'nexus-wp-link-shortener') . '</p></div>';
            });
        }
    }
    
    /**
     * Update existing link
     */
    private function update_existing_link() {
        $link_id = intval($_POST['link_id']);
        
        if (!Nexus_Links_Permissions::can_edit_link($link_id)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('You cannot edit this link.', 'nexus-wp-link-shortener') . '</p></div>';
            });
            return;
        }
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'campaign' => sanitize_text_field($_POST['campaign']),
            'description' => sanitize_textarea_field($_POST['description']),
            'target_url' => esc_url_raw($_POST['target_url']),
            'http_status' => intval($_POST['http_status']),
            'active' => isset($_POST['active']) ? 1 : 0
        );
        
        $result = Nexus_Links_Database::update_link($link_id, $data);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Link updated successfully!', 'nexus-wp-link-shortener') . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Failed to update link.', 'nexus-wp-link-shortener') . '</p></div>';
            });
        }
    }
    
    /**
     * Delete existing link
     */
    private function delete_existing_link() {
        $link_id = intval($_POST['link_id']);
        
        if (!Nexus_Links_Permissions::can_edit_link($link_id)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('You cannot delete this link.', 'nexus-wp-link-shortener') . '</p></div>';
            });
            return;
        }
        
        $result = Nexus_Links_Database::delete_link($link_id);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Link deleted successfully!', 'nexus-wp-link-shortener') . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Failed to delete link.', 'nexus-wp-link-shortener') . '</p></div>';
            });
        }
    }
}