<?php
/**
 * Database management class
 */
class Nexus_Links_Database {
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Links table
        $links_table = $wpdb->prefix . 'nexus_links';
        $links_sql = "CREATE TABLE $links_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            post_type varchar(20) NOT NULL DEFAULT 'post',
            slug varchar(6) NOT NULL,
            name varchar(255) NOT NULL,
            campaign varchar(100) DEFAULT NULL,
            description text DEFAULT NULL,
            target_url text DEFAULT NULL,
            http_status int(3) NOT NULL DEFAULT 302,
            active tinyint(1) NOT NULL DEFAULT 1,
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY post_id (post_id),
            KEY post_type (post_type),
            KEY active (active),
            KEY created_by (created_by),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Clicks table
        $clicks_table = $wpdb->prefix . 'nexus_clicks';
        $clicks_sql = "CREATE TABLE $clicks_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            link_id bigint(20) NOT NULL,
            timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            referrer text DEFAULT NULL,
            user_agent text DEFAULT NULL,
            device varchar(50) DEFAULT NULL,
            browser varchar(50) DEFAULT NULL,
            os varchar(50) DEFAULT NULL,
            anonymized_ip varchar(45) DEFAULT NULL,
            country varchar(2) DEFAULT NULL,
            region varchar(100) DEFAULT NULL,
            is_bot tinyint(1) NOT NULL DEFAULT 0,
            utm_source varchar(100) DEFAULT NULL,
            utm_medium varchar(100) DEFAULT NULL,
            utm_campaign varchar(100) DEFAULT NULL,
            utm_term varchar(100) DEFAULT NULL,
            utm_content varchar(100) DEFAULT NULL,
            query_params_json text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY link_id (link_id),
            KEY timestamp (timestamp),
            KEY is_bot (is_bot),
            KEY country (country),
            FOREIGN KEY (link_id) REFERENCES $links_table(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($links_sql);
        dbDelta($clicks_sql);
        
        // Update database version
        update_option('nexus_links_db_version', NEXUS_LINKS_VERSION);
        
        // Schedule cleanup event
        if (!wp_next_scheduled('nexus_links_cleanup_analytics')) {
            wp_schedule_event(time(), 'daily', 'nexus_links_cleanup_analytics');
        }
    }
    
    /**
     * Get a link by slug
     */
    public static function get_link_by_slug($slug) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nexus_links';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE slug = %s AND active = 1",
            $slug
        ));
    }
    
    /**
     * Get links for a post
     */
    public static function get_links_by_post($post_id, $post_type = 'post') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nexus_links';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE post_id = %d AND post_type = %s ORDER BY created_at DESC",
            $post_id,
            $post_type
        ));
    }
    
    /**
     * Create a new link
     */
    public static function create_link($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nexus_links';
        
        // Generate unique slug
        $slug = self::generate_unique_slug();
        
        $result = $wpdb->insert(
            $table,
            array(
                'post_id' => $data['post_id'],
                'post_type' => $data['post_type'],
                'slug' => $slug,
                'name' => $data['name'],
                'campaign' => $data['campaign'] ?? null,
                'description' => $data['description'] ?? null,
                'target_url' => $data['target_url'] ?? null,
                'http_status' => $data['http_status'] ?? 302,
                'active' => $data['active'] ?? 1,
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s')
        );
        
        if ($result !== false) {
            return array(
                'id' => $wpdb->insert_id,
                'slug' => $slug,
                'url' => home_url('/' . $slug)
            );
        }
        
        return false;
    }
    
    /**
     * Generate unique 6-character slug
     */
    private static function generate_unique_slug() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nexus_links';
        $attempts = 0;
        $max_attempts = 100;
        
        do {
            $slug = self::random_string(6);
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE slug = %s",
                $slug
            ));
            $attempts++;
        } while ($exists > 0 && $attempts < $max_attempts);
        
        if ($attempts >= $max_attempts) {
            throw new Exception(__('Could not generate unique slug', 'nexus-wp-link-shortener'));
        }
        
        return $slug;
    }
    
    /**
     * Generate random string
     */
    private static function random_string($length = 6) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        
        return $result;
    }
    
    /**
     * Record a click
     */
    public static function record_click($link_id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nexus_clicks';
        
        return $wpdb->insert(
            $table,
            array(
                'link_id' => $link_id,
                'timestamp' => current_time('mysql'),
                'referrer' => $data['referrer'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'device' => $data['device'] ?? null,
                'browser' => $data['browser'] ?? null,
                'os' => $data['os'] ?? null,
                'anonymized_ip' => $data['anonymized_ip'] ?? null,
                'country' => $data['country'] ?? null,
                'region' => $data['region'] ?? null,
                'is_bot' => $data['is_bot'] ?? 0,
                'utm_source' => $data['utm_source'] ?? null,
                'utm_medium' => $data['utm_medium'] ?? null,
                'utm_campaign' => $data['utm_campaign'] ?? null,
                'utm_term' => $data['utm_term'] ?? null,
                'utm_content' => $data['utm_content'] ?? null,
                'query_params_json' => $data['query_params_json'] ?? null
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Update link
     */
    public static function update_link($id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nexus_links';
        
        return $wpdb->update(
            $table,
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
    }
    
    /**
     * Delete link
     */
    public static function delete_link($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nexus_links';
        
        return $wpdb->delete(
            $table,
            array('id' => $id),
            array('%d')
        );
    }
}