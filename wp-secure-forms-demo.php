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

// Define plugin constants
define('WPSFD_VERSION', '1.0.0');
define('WPSFD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPSFD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPSFD_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader for plugin classes
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'WPSFD\\') === 0) {
        $class_name = str_replace('WPSFD\\', '', $class_name);
        $class_name = str_replace('\\', '/', $class_name);
        $class_name = str_replace('_', '-', $class_name);
        $file_path = WPSFD_PLUGIN_DIR . 'includes/class-' . strtolower($class_name) . '.php';
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
});

// Activation hook
register_activation_hook(__FILE__, 'wpsfd_activate_plugin');

function wpsfd_activate_plugin() {
    // Create database tables
    WPSFD\Database_Manager::create_tables();
}

// Initialize the plugin
function wpsfd_init_plugin() {
    $plugin_loader = new WPSFD\Plugin_Loader();
    $plugin_loader->init();
}

add_action('plugins_loaded', 'wpsfd_init_plugin');