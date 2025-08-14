<?php
/**
 * Permissions and capability management class
 */
class Nexus_Links_Permissions {
    
    public function __construct() {
        // Constructor placeholder
    }
    
    /**
     * Check if user can manage links
     */
    public static function can_manage_links() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $allowed_roles = get_option('nexus_links_allowed_roles', array('administrator', 'editor'));
        $user = wp_get_current_user();
        
        foreach ($allowed_roles as $role) {
            if (in_array($role, $user->roles)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user can edit specific link
     */
    public static function can_edit_link($link_id) {
        if (!self::can_manage_links()) {
            return false;
        }
        
        // Administrators can edit all links
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Users can edit their own links
        global $wpdb;
        $table = $wpdb->prefix . 'nexus_links';
        $created_by = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM $table WHERE id = %d",
            $link_id
        ));
        
        return $created_by == get_current_user_id();
    }
}