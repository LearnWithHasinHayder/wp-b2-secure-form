<?php
/**
 * Plugin Name: WP Secure Forms Demo
 * Plugin URI: https://example.com/wp-secure-forms-demo
 * Description: A WordPress plugin demonstrating secure vs insecure data handling and safe file uploads.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: wp-secure-forms-demo
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants for backward compatibility
define('WPSFD_VERSION', '1.0.0');
define('WPSFD_PLUGIN_DIR', __DIR__);
define('WPSFD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPSFD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 *
 * Handles plugin initialization and activation.
 */
class WP_Secure_Forms_Demo {

    /**
     * Initialize the plugin
     */
    public static function init() {
        // Load the Plugin_Loader class manually first
        require_once WPSFD_PLUGIN_DIR . '/includes/class-plugin-loader.php';

        // Register activation hook
        register_activation_hook(__FILE__, [self::class, 'activate']);

        // Initialize plugin on plugins_loaded
        add_action('plugins_loaded', [self::class, 'load']);
    }

    /**
     * Plugin activation hook
     */
    public static function activate() {
        // Load Database_Manager class for activation
        require_once WPSFD_PLUGIN_DIR . '/includes/class-database-manager.php';
        // Create database tables
        WPSFD\Database_Manager::create_tables();
    }

    /**
     * Load the plugin
     */
    public static function load() {
        $plugin_loader = new WPSFD\Plugin_Loader();
        $plugin_loader->init();
    }
}

// Initialize the plugin
WP_Secure_Forms_Demo::init();