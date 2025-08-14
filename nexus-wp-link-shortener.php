<?php
/**
 * Plugin Name: Nexus WP Link Shortener
 * Plugin URI: https://github.com/Superkikim/nexus-wp-link-shortener
 * Description: A 100% internal WordPress link shortener with comprehensive analytics and no external dependencies.
 * Version: 1.0.0
 * Author: Superkikim
 * Author Email: superkikim@sissaoui.com
 * Text Domain: nexus-wp-link-shortener
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NEXUS_LINKS_VERSION', '1.0.0');
define('NEXUS_LINKS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NEXUS_LINKS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NEXUS_LINKS_PLUGIN_FILE', __FILE__);

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('Nexus_WP_Link_Shortener', 'activate'));
register_deactivation_hook(__FILE__, array('Nexus_WP_Link_Shortener', 'deactivate'));

// Load the main plugin class
require_once NEXUS_LINKS_PLUGIN_DIR . 'includes/class-nexus-wp-link-shortener.php';

// Load database class for activation hook
require_once NEXUS_LINKS_PLUGIN_DIR . 'includes/class-database.php';

// Initialize the plugin
function nexus_wp_link_shortener_init() {
    new Nexus_WP_Link_Shortener();
}
add_action('plugins_loaded', 'nexus_wp_link_shortener_init');