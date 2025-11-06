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
            <h1>Secure Form</h1>
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
     * Display the secure form with reCAPTCHA.
     */
    public static function display_recaptcha_form() {
        ?>
        <div class="wrap">
            <h1>Secure Form with reCAPTCHA v3 Checkbox</h1>
            <p>This form demonstrates secure data handling with nonce validation, input sanitization, prepared statements, and reCAPTCHA v3 checkbox protection.</p>

            <form id="wpsfd-recaptcha-form" method="post">
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
                    <tr>
                        <th><label for="recaptcha">reCAPTCHA</label></th>
                        <td>
                            <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Submit Securely with reCAPTCHA">
                </p>
            </form>

            <div id="wpsfd-recaptcha-response"></div>
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

        // Verify reCAPTCHA if token is provided
        if (isset($_POST['recaptcha_token'])) {
            $recaptcha_secret = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'; // Test secret key
            $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $recaptcha_secret,
                    'response' => $_POST['recaptcha_token'],
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ]
            ]);

            if (is_wp_error($response)) {
                wp_send_json_error(['message' => 'reCAPTCHA verification failed.']);
            }

            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);

            if (!$result['success']) {
                wp_send_json_error(['message' => 'reCAPTCHA verification failed. Please try again.']);
            }
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