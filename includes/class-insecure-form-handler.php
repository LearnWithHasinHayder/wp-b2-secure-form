<?php
/**
 * Insecure Form Handler Class
 *
 * Demonstrates insecure form handling without nonce, sanitization, or prepared statements.
 * WARNING: This is for educational purposes only. Do not use in production!
 */

namespace WPSFD;

class Insecure_Form_Handler {

    /**
     * Display the insecure form.
     */
    public static function display_form() {
        ?>
        <div class="wrap">
            <h1>Insecure Form Demo</h1>
            <p><strong>WARNING:</strong> This form demonstrates insecure data handling. It does not use nonce validation, input sanitization, or prepared statements. This can lead to SQL injection and XSS attacks.</p>

            <form id="wpsfd-insecure-form" method="post">
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
                    <input type="submit" class="button button-primary" value="Submit Insecurely">
                </p>
            </form>

            <div id="wpsfd-insecure-response"></div>
        </div>
        <?php
    }

    /**
     * Handle AJAX submission for insecure form.
     * WARNING: This is intentionally insecure for demonstration purposes.
     */
    public static function handle_ajax_submission() {
        // No nonce check - vulnerable to CSRF

        // Raw POST data - no sanitization
        $name = $_POST['name'];
        $email = $_POST['email'];
        $message = $_POST['message'];

        // No validation

        // Unsafe SQL insertion using string interpolation
        global $wpdb;
        $table = $wpdb->prefix . Database_Manager::SUBMISSIONS_TABLE;

        // This is vulnerable to SQL injection!
        $sql = "INSERT INTO $table (name, email, message, is_secure) VALUES ('$name', '$email', '$message', 0)";
        $result = $wpdb->query($sql);

        if ($result) {
            wp_send_json_success(['message' => 'Submission saved (insecurely)!']);
        } else {
            wp_send_json_error(['message' => 'Failed to save submission.']);
        }
    }
}