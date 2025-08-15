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
        add_action('wp_ajax_nexus_get_last_link', array($this, 'ajax_get_last_link'));
        add_action('wp_ajax_nexus_reset_all_data', array($this, 'ajax_reset_all_data'));
        add_action('wp_ajax_nexus_remove_all_links', array($this, 'ajax_remove_all_links'));
        add_action('admin_post_nexus_delete_link', array($this, 'handle_delete_link'));
        add_action('admin_notices', array($this, 'display_admin_notices'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        $capability = 'edit_posts';
        
        add_menu_page(
            __('Nexus Links', 'nexus-wp-link-shortener'),
            __('Nexus Links', 'nexus-wp-link-shortener'),
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
        
        // Add data management page
        add_submenu_page(
            'nexus-links',
            __('Data Management', 'nexus-wp-link-shortener'),
            __('Data Management', 'nexus-wp-link-shortener'),
            'manage_options',
            'nexus-links-data-management',
            array($this, 'data_management_page')
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
        
        // Hidden add/edit link page
        add_submenu_page(
            null, // Hidden from menu
            __('Add/Edit Link', 'nexus-wp-link-shortener'),
            __('Add/Edit Link', 'nexus-wp-link-shortener'),
            $capability,
            'nexus-links-add-edit',
            array($this, 'add_edit_link_page')
        );
    }
    
    /**
     * Get supported custom post types
     */
    private function get_supported_post_types() {
        $enabled_post_types = get_option('nexus_links_enabled_post_types', array('post', 'page'));
        $all_cpts = get_post_types(array(
            'public' => true,
            'show_ui' => true,
            '_builtin' => false
        ), 'names');
        
        return array_filter($all_cpts, function($cpt) use ($enabled_post_types) {
            return $cpt !== 'attachment' && in_array($cpt, $enabled_post_types);
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
     * Data management page
     */
    public function data_management_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Get current statistics
        global $wpdb;
        $links_table = $wpdb->prefix . 'nexus_links';
        $clicks_table = $wpdb->prefix . 'nexus_clicks';
        
        $total_links = $wpdb->get_var("SELECT COUNT(*) FROM $links_table");
        $total_clicks = $wpdb->get_var("SELECT COUNT(*) FROM $clicks_table");
        $active_links = $wpdb->get_var("SELECT COUNT(*) FROM $links_table WHERE active = 1");
        
        require_once NEXUS_LINKS_PLUGIN_DIR . 'templates/admin-data-management.php';
    }
    
    /**
     * AJAX handler for removing all links
     */
    public function ajax_remove_all_links() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexus_links_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        global $wpdb;
        $links_table = $wpdb->prefix . 'nexus_links';
        $clicks_table = $wpdb->prefix . 'nexus_clicks';
        
        // Delete clicks first (foreign key constraint)
        $clicks_deleted = $wpdb->query("DELETE FROM $clicks_table");
        
        // Delete links
        $links_deleted = $wpdb->query("DELETE FROM $links_table");
        
        if ($links_deleted !== false) {
            wp_send_json_success(array(
                'message' => sprintf(__('Successfully removed %d links and %d click records.', 'nexus-wp-link-shortener'), $links_deleted, $clicks_deleted),
                'links_deleted' => $links_deleted,
                'clicks_deleted' => $clicks_deleted
            ));
        } else {
            wp_send_json_error(__('Failed to remove links. Please try again.', 'nexus-wp-link-shortener'));
        }
    }
    
    /**
     * AJAX handler for resetting all analytics data
     */
    public function ajax_reset_all_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexus_links_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        global $wpdb;
        $clicks_table = $wpdb->prefix . 'nexus_clicks';
        
        // Delete all analytics data
        $clicks_deleted = $wpdb->query("DELETE FROM $clicks_table");
        
        if ($clicks_deleted !== false) {
            wp_send_json_success(array(
                'message' => sprintf(__('Successfully reset analytics data. Removed %d click records.', 'nexus-wp-link-shortener'), $clicks_deleted),
                'clicks_deleted' => $clicks_deleted
            ));
        } else {
            wp_send_json_error(__('Failed to reset analytics data. Please try again.', 'nexus-wp-link-shortener'));
        }
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
     * AJAX handler for getting analytics data
     */
    public function ajax_get_analytics() {
        // Handle both POST and GET requests for flexibility
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : (isset($_GET['nonce']) ? $_GET['nonce'] : '');
        
        if (!wp_verify_nonce($nonce, 'nexus_links_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }
        
        $date_range = isset($_POST['date_range']) ? sanitize_text_field($_POST['date_range']) : 
        
        $analytics = new Nexus_Links_Analytics();
        $data = $analytics->get_dashboard_data($date_range);
        
        // Ensure data is properly formatted
        if (!$data) {
            $data = array(
                'total_clicks' => 0,
                'unique_clicks' => 0,
                'human_clicks' => 0,
                'bot_clicks' => 0,
                'top_referrers' => array(),
                'clicks_by_day' => array(),
                'device_breakdown' => array(),
                'browser_breakdown' => array()
            );
        }
        
        wp_send_json_success($data);
    }
    
    /**
     * AJAX handler for getting last created link
     */
    public function ajax_get_last_link() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexus_links_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }
        
        $post_type = sanitize_text_field($_POST['post_type']);
        
        global $wpdb;
        $table = $wpdb->prefix . 'nexus_links';
        
        $last_link = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE post_type = %s ORDER BY created_at DESC LIMIT 1",
            $post_type
        ));
        
        if ($last_link) {
            $url = Nexus_Links_URL_Handler::get_short_url($last_link->slug);
            wp_send_json_success(array('url' => $url));
        } else {
            wp_send_json_error(__('No links found for this content type.', 'nexus-wp-link-shortener'));
        }
    }
    
    /**
     * AJAX handler for analytics cleanup
     */
    public function ajax_cleanup_analytics() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexus_links_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $plugin = Nexus_WP_Link_Shortener::get_instance();
        $plugin->scheduled_cleanup();
        
        wp_send_json_success(array('message' => __('Analytics cleanup completed successfully.', 'nexus-wp-link-shortener')));
    }
    
    /**
     * AJAX handler for data export
     */
    public function ajax_export_data() {
        if (!wp_verify_nonce($_GET['nonce'], 'nexus_links_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        global $wpdb;
        
        $links_table = $wpdb->prefix . 'nexus_links';
        $clicks_table = $wpdb->prefix . 'nexus_clicks';
        
        $links = $wpdb->get_results("SELECT * FROM $links_table ORDER BY created_at DESC");
        $clicks = $wpdb->get_results("SELECT * FROM $clicks_table ORDER BY timestamp DESC");
        
        $export_data = array(
            'export_date' => current_time('mysql'),
            'links' => $links,
            'clicks' => $clicks
        );
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="nexus-links-export-' . date('Y-m-d') . '.json"');
        echo json_encode($export_data, JSON_PRETTY_PRINT);
        exit;
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
            'allowed_roles' => isset($_POST['allowed_roles']) ? $_POST['allowed_roles'] : array(),
            'enabled_post_types' => isset($_POST['enabled_post_types']) ? $_POST['enabled_post_types'] : array('post', 'page'),
            'remove_www_prefix' => isset($_POST['remove_www_prefix']),
            'remove_go_segment' => isset($_POST['remove_go_segment'])
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
        
        $links = Nexus_Links_Database::get_links_by_post($post_id, $post_type);
        $source_page = $this->get_source_page_info($post_type);
        
        require_once NEXUS_LINKS_PLUGIN_DIR . 'templates/admin-manage-links.php';
    }
    
    /**
     * Add/Edit link page
     */
    public function add_edit_link_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'post';
        $link_id = isset($_GET['link_id']) ? intval($_GET['link_id']) : 0;
        $action = $link_id ? 'edit' : 'add';
        
        if (!$post_id) {
            wp_die(__('Invalid post ID.', 'nexus-wp-link-shortener'));
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_die(__('Post not found.', 'nexus-wp-link-shortener'));
        }
        
        $link = null;
        if ($link_id) {
            global $wpdb;
            $table = $wpdb->prefix . 'nexus_links';
            $link = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d AND post_id = %d",
                $link_id, $post_id
            ));
            
            if (!$link) {
                wp_die(__('Link not found.', 'nexus-wp-link-shortener'));
            }
            
            if (!Nexus_Links_Permissions::can_edit_link($link_id)) {
                wp_die(__('You cannot edit this link.', 'nexus-wp-link-shortener'));
            }
        }
        
        // Handle form submissions
        if (isset($_POST['action'])) {
            $this->handle_add_edit_form_submission($post_id, $post_type, $link_id);
        }
        
        require_once NEXUS_LINKS_PLUGIN_DIR . 'templates/admin-add-edit-link.php';
    }
    
    /**
     * Get source page information for breadcrumb
     */
    private function get_source_page_info($post_type) {
        $post_type_object = get_post_type_object($post_type);
        
        $page_mapping = array(
            'post' => array(
                'title' => __('Posts', 'nexus-wp-link-shortener'),
                'url' => admin_url('admin.php?page=nexus-links-posts')
            ),
            'page' => array(
                'title' => __('Pages', 'nexus-wp-link-shortener'),
                'url' => admin_url('admin.php?page=nexus-links-pages')
            )
        );
        
        if (isset($page_mapping[$post_type])) {
            return $page_mapping[$post_type];
        }
        
        // For custom post types
        return array(
            'title' => $post_type_object ? $post_type_object->labels->name : ucfirst($post_type),
            'url' => admin_url('admin.php?page=nexus-links-' . $post_type)
        );
    }
    
    /**
     * Handle add/edit form submission
     */
    private function handle_add_edit_form_submission($post_id, $post_type, $link_id = 0) {
        if (!wp_verify_nonce($_POST['nexus_links_nonce'], 'nexus_links_add_edit')) {
            return;
        }
        
        $action = sanitize_text_field($_POST['action']);
        
        if ($action === 'create_link') {
            $this->create_new_link($post_id, $post_type);
        } elseif ($action === 'update_link' && $link_id) {
            $this->update_existing_link($link_id);
        }
        
        // Redirect back to manage links page
        $redirect_url = admin_url('admin.php?page=nexus-links-manage&post_id=' . $post_id . '&post_type=' . $post_type);
        wp_redirect($redirect_url);
        exit;
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
            $this->add_admin_notice(__('Link created successfully!', 'nexus-wp-link-shortener'), 'success');
        } else {
            $this->add_admin_notice(__('Failed to create link.', 'nexus-wp-link-shortener'), 'error');
        }
    }
    
    /**
     * Update existing link
     */
    private function update_existing_link($link_id) {
        
        if (!Nexus_Links_Permissions::can_edit_link($link_id)) {
            $this->add_admin_notice(__('You cannot edit this link.', 'nexus-wp-link-shortener'), 'error');
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
            $this->add_admin_notice(__('Link updated successfully!', 'nexus-wp-link-shortener'), 'success');
        } else {
            $this->add_admin_notice(__('Failed to update link.', 'nexus-wp-link-shortener'), 'error');
        }
    }
    
    /**
     * Delete existing link
     */
    public function handle_delete_link() {
        if (!wp_verify_nonce($_POST['nexus_links_nonce'], 'nexus_links_delete')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }
        
        $link_id = intval($_POST['link_id']);
        $post_id = intval($_POST['post_id']);
        $post_type = sanitize_text_field($_POST['post_type']);
        
        if (!Nexus_Links_Permissions::can_edit_link($link_id)) {
            wp_die('You cannot delete this link');
        }
        
        $result = Nexus_Links_Database::delete_link($link_id);
        
        if ($result !== false) {
            $this->add_admin_notice(__('Link deleted successfully!', 'nexus-wp-link-shortener'), 'success');
        } else {
            $this->add_admin_notice(__('Failed to delete link.', 'nexus-wp-link-shortener'), 'error');
        }
        
        // Redirect back to manage links page
        $redirect_url = admin_url('admin.php?page=nexus-links-manage&post_id=' . $post_id . '&post_type=' . $post_type);
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Add admin notice
     */
    private function add_admin_notice($message, $type = 'success') {
        set_transient('nexus_links_admin_notice', array(
            'message' => $message,
            'type' => $type
        ), 30);
    }
    
    /**
     * Display admin notices
     */
    public function display_admin_notices() {
        $notice = get_transient('nexus_links_admin_notice');
        if ($notice) {
            echo '<div class="notice notice-' . esc_attr($notice['type']) . ' is-dismissible"><p>' . esc_html($notice['message']) . '</p></div>';
            delete_transient('nexus_links_admin_notice');
        }
    }
}