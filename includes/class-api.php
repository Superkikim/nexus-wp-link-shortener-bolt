<?php
/**
 * REST API endpoints class
 */
class Nexus_Links_API {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('nexus-links/v1', '/links', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_links'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route('nexus-links/v1', '/links', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_link'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route('nexus-links/v1', '/links/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_link'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route('nexus-links/v1', '/links/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_link'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route('nexus-links/v1', '/analytics', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_analytics'),
            'permission_callback' => array($this, 'check_permissions')
        ));
    }
    
    /**
     * Check permissions for API access
     */
    public function check_permissions() {
        return Nexus_Links_Permissions::can_manage_links();
    }
    
    /**
     * Get links endpoint
     */
    public function get_links($request) {
        $post_id = $request->get_param('post_id');
        $post_type = $request->get_param('post_type');
        
        if ($post_id && $post_type) {
            $links = Nexus_Links_Database::get_links_by_post($post_id, $post_type);
        } else {
            // Get all links (with pagination)
            global $wpdb;
            $table = $wpdb->prefix . 'nexus_links';
            $links = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 100");
        }
        
        return rest_ensure_response($links);
    }
    
    /**
     * Create link endpoint
     */
    public function create_link($request) {
        $data = array(
            'post_id' => intval($request->get_param('post_id')),
            'post_type' => sanitize_text_field($request->get_param('post_type')),
            'name' => sanitize_text_field($request->get_param('name')),
            'campaign' => sanitize_text_field($request->get_param('campaign')),
            'description' => sanitize_textarea_field($request->get_param('description')),
            'target_url' => esc_url_raw($request->get_param('target_url')),
            'http_status' => intval($request->get_param('http_status')) ?: 302,
            'active' => $request->get_param('active') ? 1 : 0
        );
        
        $result = Nexus_Links_Database::create_link($data);
        
        if ($result) {
            return rest_ensure_response($result);
        } else {
            return new WP_Error('creation_failed', 'Failed to create link', array('status' => 500));
        }
    }
    
    /**
     * Update link endpoint
     */
    public function update_link($request) {
        $id = intval($request->get_param('id'));
        
        if (!Nexus_Links_Permissions::can_edit_link($id)) {
            return new WP_Error('insufficient_permissions', 'You cannot edit this link', array('status' => 403));
        }
        
        $data = array();
        
        if ($request->has_param('name')) {
            $data['name'] = sanitize_text_field($request->get_param('name'));
        }
        
        if ($request->has_param('campaign')) {
            $data['campaign'] = sanitize_text_field($request->get_param('campaign'));
        }
        
        if ($request->has_param('description')) {
            $data['description'] = sanitize_textarea_field($request->get_param('description'));
        }
        
        if ($request->has_param('target_url')) {
            $data['target_url'] = esc_url_raw($request->get_param('target_url'));
        }
        
        if ($request->has_param('http_status')) {
            $data['http_status'] = intval($request->get_param('http_status'));
        }
        
        if ($request->has_param('active')) {
            $data['active'] = $request->get_param('active') ? 1 : 0;
        }
        
        $result = Nexus_Links_Database::update_link($id, $data);
        
        if ($result !== false) {
            return rest_ensure_response(array('success' => true));
        } else {
            return new WP_Error('update_failed', 'Failed to update link', array('status' => 500));
        }
    }
    
    /**
     * Delete link endpoint
     */
    public function delete_link($request) {
        $id = intval($request->get_param('id'));
        
        if (!Nexus_Links_Permissions::can_edit_link($id)) {
            return new WP_Error('insufficient_permissions', 'You cannot delete this link', array('status' => 403));
        }
        
        $result = Nexus_Links_Database::delete_link($id);
        
        if ($result !== false) {
            return rest_ensure_response(array('success' => true));
        } else {
            return new WP_Error('deletion_failed', 'Failed to delete link', array('status' => 500));
        }
    }
    
    /**
     * Get analytics endpoint
     */
    public function get_analytics($request) {
        $date_range = $request->get_param('date_range') ?: '30';
        
        $analytics = new Nexus_Links_Analytics();
        $data = $analytics->get_dashboard_data($date_range);
        
        return rest_ensure_response($data);
    }
}