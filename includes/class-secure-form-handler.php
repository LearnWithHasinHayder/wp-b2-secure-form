<?php
/**
 * Secure Form Handler Class
 *
 * Demonstrates secure form handling with nonce validation, sanitization, and prepared statements.
 */

namespace WPSFD;

class Secure_Form_Handler {

    /**
     * Display the secure form.
     */
    public static function display_form() {
        ?>
        <div class="wrap">
            <h1>Secure Form Demo</h1>
            <p>This form demonstrates secure data handling with nonce validation, input sanitization, and prepared statements.</p>

            <form id="wpsfd-secure-form" method="post">
                <?php wp_nonce_field('wpsfd_secure_submit', 'wpsfd_secure_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="name">Name</label></th>
                        <td><input type="text" id="name" name="name" required></td>
                    </tr>
                    <tr>
                        <th><label for="email">Email</label></th>
                        <td><input type="email" id="email" name="email" required></td>
                    </tr>
                    <tr>
                        <th><label for="message">Message</label></th>
                        <td><textarea id="message" name="message" rows="5" required></textarea></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Submit Securely">
                </p>
            </form>

            <div id="wpsfd-secure-response"></div>
        </div>
        <?php
    }

    /**
     * Handle AJAX submission for secure form.
     */
    public static function handle_ajax_submission() {
        // Check nonce for security
        if (!wp_verify_nonce($_POST['wpsfd_secure_nonce'], 'wpsfd_secure_submit')) {
            wp_send_json_error(['message' => 'Security check failed.']);
        }

        // Sanitize inputs
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);

        // Validate inputs
        if (empty($name) || empty($email) || empty($message)) {
            wp_send_json_error(['message' => 'All fields are required.']);
        }

        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Invalid email address.']);
        }

        // Insert using prepared statement (via Database_Manager)
        $data = [
            'name' => $name,
            'email' => $email,
            'message' => $message,
        ];

        $result = Database_Manager::insert_submission($data, true);

        if ($result) {
            wp_send_json_success(['message' => 'Submission saved securely!']);
        } else {
            wp_send_json_error(['message' => 'Failed to save submission.']);
        }
    }
}