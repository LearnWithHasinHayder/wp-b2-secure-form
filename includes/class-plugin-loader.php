<?php
/**
 * Plugin Loader Class
 *
 * Handles plugin initialization and hook registration.
 */

namespace WPSFD;

class Plugin_Loader {

    /**
     * Initialize the plugin.
     */
    public function init() {
        // Load all classes
        $this->load_classes();

        // Register hooks
        $this->register_hooks();
    }

    /**
     * Load all plugin classes.
     */
    private function load_classes() {
        // Load class files individually
        require_once WPSFD_PLUGIN_DIR . '/includes/class-database-manager.php';
        require_once WPSFD_PLUGIN_DIR . '/includes/class-secure-form-handler.php';
        require_once WPSFD_PLUGIN_DIR . '/includes/class-insecure-form-handler.php';
        require_once WPSFD_PLUGIN_DIR . '/includes/class-file-upload-handler.php';
        require_once WPSFD_PLUGIN_DIR . '/includes/class-admin-menu.php';
        require_once WPSFD_PLUGIN_DIR . '/includes/class-assets-manager.php';

        // Instantiate classes if needed
        new Database_Manager();
        new Secure_Form_Handler();
        new Insecure_Form_Handler();
        new File_Upload_Handler();
        new Admin_Menu();
        new Assets_Manager();
    }

    /**
     * Register WordPress hooks.
     */
    private function register_hooks() {
        // Admin menu
        add_action('admin_menu', [Admin_Menu::class, 'add_menu']);

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', [Assets_Manager::class, 'enqueue_assets']);

        // AJAX handlers
        add_action('wp_ajax_wpsfd_secure_submit', [Secure_Form_Handler::class, 'handle_ajax_submission']);
        add_action('wp_ajax_wpsfd_insecure_submit', [Insecure_Form_Handler::class, 'handle_ajax_submission']);
        add_action('wp_ajax_wpsfd_upload_file', [File_Upload_Handler::class, 'handle_ajax_upload']);

        // Admin post handlers for deletes
        // add_action('admin_post_delete_submission', [Admin_Menu::class, 'handle_delete_submission']);
        // add_action('admin_post_delete_upload', [Admin_Menu::class, 'handle_delete_upload']);
    }
}