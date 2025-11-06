<?php
/**
 * Assets Manager Class
 *
 * Handles enqueuing CSS and JavaScript assets.
 */

namespace WPSFD;

class Assets_Manager {

    /**
     * Enqueue assets on admin pages.
     */
    public static function enqueue_assets($hook) {
        // Only enqueue on our plugin pages
        $plugin_pages = [
            'toplevel_page_wp-secure-forms-demo',
            'wp-secure-demo_page_wp-secure-forms-demo-secure',
            'wp-secure-demo_page_wp-secure-forms-demo-insecure',
            'wp-secure-demo_page_wp-secure-forms-demo-upload',
            'wp-secure-demo_page_wp-secure-forms-demo-submissions',
        ];

        if (!in_array($hook, $plugin_pages)) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'wpsfd-admin-styles',
            WPSFD_PLUGIN_URL . 'assets/css/style.css',
            [],
            WPSFD_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'wpsfd-admin-scripts',
            WPSFD_PLUGIN_URL . 'assets/js/form-handler.js',
            ['jquery'],
            WPSFD_VERSION,
            true
        );

        // Localize script with AJAX data
        wp_localize_script('wpsfd-admin-scripts', 'wpsfd_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'secure_nonce' => wp_create_nonce('wpsfd_secure_submit'),
            'insecure_nonce' => wp_create_nonce('wpsfd_insecure_submit'), // Note: insecure form doesn't use this
            'upload_nonce' => wp_create_nonce('wpsfd_upload_file'),
        ]);
    }
}