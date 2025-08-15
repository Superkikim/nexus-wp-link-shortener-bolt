<?php
/**
 * Main plugin class
 */
class Nexus_WP_Link_Shortener {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once NEXUS_LINKS_PLUGIN_DIR . 'includes/class-database.php';
        require_once NEXUS_LINKS_PLUGIN_DIR . 'includes/class-admin.php';
        require_once NEXUS_LINKS_PLUGIN_DIR . 'includes/class-frontend.php';
        require_once NEXUS_LINKS_PLUGIN_DIR . 'includes/class-api.php';
        require_once NEXUS_LINKS_PLUGIN_DIR . 'includes/class-analytics.php';
        require_once NEXUS_LINKS_PLUGIN_DIR . 'includes/class-url-handler.php';
        require_once NEXUS_LINKS_PLUGIN_DIR . 'includes/class-permissions.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize components
        new Nexus_Links_Database();
        new Nexus_Links_Admin();
        new Nexus_Links_Frontend();
        new Nexus_Links_API();
        new Nexus_Links_Analytics();
        new Nexus_Links_URL_Handler();
        new Nexus_Links_Permissions();
        
        // Plugin hooks
        add_action('init', array($this, 'load_textdomain'));
        add_action('init', array($this, 'maybe_flush_rewrite_rules'));
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('nexus-wp-link-shortener', false, dirname(plugin_basename(NEXUS_LINKS_PLUGIN_FILE)) . '/languages/');
    }
    
    /**
     * Maybe flush rewrite rules if needed
     */
    public function maybe_flush_rewrite_rules() {
        if (get_option('nexus_links_flush_rewrite_rules', false)) {
            flush_rewrite_rules();
            delete_option('nexus_links_flush_rewrite_rules');
        }
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        // Create database tables
        Nexus_Links_Database::create_tables();
        
        // Set flush rewrite rules flag
        update_option('nexus_links_flush_rewrite_rules', true);
        
        // Set default options
        self::set_default_options();
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $defaults = array(
            'retention_period' => 13, // months
            'forward_utm_params' => false,
            'allowed_roles' => array('administrator', 'editor'),
            'analytics_enabled' => true,
            'bot_detection_enabled' => true,
            'enabled_post_types' => array('post', 'page')
        );
        
        foreach ($defaults as $key => $value) {
            if (false === get_option('nexus_links_' . $key)) {
                update_option('nexus_links_' . $key, $value);
            }
        }
    }
    
    /**
     * Scheduled cleanup of old analytics data
     */
    public function scheduled_cleanup() {
        global $wpdb;
        
        $retention_months = get_option('nexus_links_retention_period', 13);
        $clicks_table = $wpdb->prefix . 'nexus_clicks';
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $clicks_table WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d MONTH)",
            $retention_months
        ));
    }
}