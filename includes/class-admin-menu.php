<?php
/**
 * Admin Menu Class
 *
 * Handles admin menu creation and page displays.
 */

namespace WPSFD;

class Admin_Menu {

    /**
     * Add the admin menu.
     */
    public static function add_menu() {
        add_menu_page(
            'WP Secure Demo',
            'WP Secure Demo',
            'manage_options',
            'wp-secure-forms-demo',
            [self::class, 'display_main_page'],
            'dashicons-shield',
            30
        );

        add_submenu_page(
            'wp-secure-forms-demo',
            'Secure Form',
            'Secure Form',
            'manage_options',
            'wp-secure-forms-demo-secure',
            [Secure_Form_Handler::class, 'display_form']
        );

        add_submenu_page(
            'wp-secure-forms-demo',
            'Secure Form with reCAPTCHA',
            'Secure Form + reCAPTCHA v3',
            'manage_options',
            'wp-secure-forms-demo-recaptcha',
            [Secure_Form_Handler::class, 'display_recaptcha_form']
        );

        add_submenu_page(
            'wp-secure-forms-demo',
            'Insecure Form',
            'Insecure Form',
            'manage_options',
            'wp-secure-forms-demo-insecure',
            [Insecure_Form_Handler::class, 'display_form']
        );

        add_submenu_page(
            'wp-secure-forms-demo',
            'File Upload',
            'File Upload',
            'manage_options',
            'wp-secure-forms-demo-upload',
            [File_Upload_Handler::class, 'display_form']
        );

        add_submenu_page(
            'wp-secure-forms-demo',
            'Submissions Viewer',
            'Submissions Viewer',
            'manage_options',
            'wp-secure-forms-demo-submissions',
            [self::class, 'display_submissions']
        );
    }

    /**
     * Display the main page.
     */
    public static function display_main_page() {
        ?>
        <div class="wrap">
            <h1>WP Secure Forms Demo</h1>
            <p>Welcome to the WordPress Secure Forms Demo plugin. This plugin demonstrates the differences between secure and insecure data handling practices.</p>

            <h2>Available Demos</h2>
            <ul>
                <li><a href="<?php echo admin_url('admin.php?page=wp-secure-forms-demo-secure'); ?>">Secure Form</a> - Demonstrates proper nonce validation, input sanitization, and prepared statements.</li>
                <li><a href="<?php echo admin_url('admin.php?page=wp-secure-forms-demo-recaptcha'); ?>">Secure Form + reCAPTCHA v3</a> - Secure form with Google reCAPTCHA v3 checkbox protection against bots.</li>
                <li><a href="<?php echo admin_url('admin.php?page=wp-secure-forms-demo-insecure'); ?>">Insecure Form</a> - Shows dangerous practices like raw SQL and no validation (for educational purposes only).</li>
                <li><a href="<?php echo admin_url('admin.php?page=wp-secure-forms-demo-upload'); ?>">File Upload</a> - Safe file upload with type and size validation.</li>
                <li><a href="<?php echo admin_url('admin.php?page=wp-secure-forms-demo-submissions'); ?>">Submissions Viewer</a> - View all form submissions and uploaded files.</li>
            </ul>

            <h2>Demo Instructions</h2>
            <ol>
                <li>Try submitting both forms with normal data and malicious input (e.g., &lt;script&gt; tags, SQL injection attempts).</li>
                <li>Test the reCAPTCHA form to see bot protection in action.</li>
                <li>Check the Submissions Viewer to see how data is stored differently.</li>
                <li>Use phpMyAdmin to inspect the database tables directly.</li>
                <li>Observe how the secure form prevents attacks while the insecure form allows them.</li>
            </ol>
        </div>
        <?php
    }

    /**
     * Display submissions and uploads.
     */
    public static function display_submissions() {
        // Handle single delete
        if (isset($_POST['delete_submission']) && isset($_POST['submission_id'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'delete_submission_' . $_POST['submission_id'])) {
                wp_die('Security check failed.');
            }
            if (Database_Manager::delete_submission(intval($_POST['submission_id']))) {
                echo '<div class="notice notice-success"><p>Submission deleted successfully.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Failed to delete submission.</p></div>';
            }
        }

        if (isset($_POST['delete_upload']) && isset($_POST['upload_id'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'delete_upload_' . $_POST['upload_id'])) {
                wp_die('Security check failed.');
            }
            if (Database_Manager::delete_upload(intval($_POST['upload_id']))) {
                echo '<div class="notice notice-success"><p>Upload deleted successfully.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Failed to delete upload.</p></div>';
            }
        }

        $submissions = Database_Manager::get_all_submissions();
        $uploads = Database_Manager::get_all_uploads();
        ?>
        <div class="wrap">
            <h1>Submissions Viewer</h1>

            <h2>Form Submissions</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Secure?</th>
                        <th>Submitted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr><td colspan="7">No submissions yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?php echo esc_html($submission['id']); ?></td>
                                <td><?php echo esc_html($submission['name']); ?></td>
                                <td><?php echo esc_html($submission['email']); ?></td>
                                <td><?php echo esc_html($submission['message']); ?></td>
                                <td><?php echo $submission['is_secure'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo esc_html($submission['created_at']); ?></td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('delete_submission_' . $submission['id']); ?>
                                        <input type="hidden" name="submission_id" value="<?php echo esc_attr($submission['id']); ?>">
                                        <input type="submit" name="delete_submission" value="Delete" class="button button-small" onclick="return confirm('Are you sure you want to delete this submission?');">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <h2>Uploaded Files</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>File Name</th>
                        <th>File Path</th>
                        <th>File URL</th>
                        <th>File Size</th>
                        <th>File Type</th>
                        <th>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($uploads)): ?>
                        <tr><td colspan="8">No uploads yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($uploads as $upload): ?>
                            <tr>
                                <td><?php echo esc_html($upload['id']); ?></td>
                                <td><?php echo esc_html($upload['file_name']); ?></td>
                                <td><?php echo esc_html($upload['file_path']); ?></td>
                                <td><a href="<?php echo esc_url($upload['file_url']); ?>" target="_blank"><?php echo esc_html($upload['file_url']); ?></a></td>
                                <td><?php echo esc_html($upload['file_size']); ?> bytes</td>
                                <td><?php echo esc_html($upload['file_type']); ?></td>
                                <td><?php echo esc_html($upload['uploaded_at']); ?></td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('delete_upload_' . $upload['id']); ?>
                                        <input type="hidden" name="upload_id" value="<?php echo esc_attr($upload['id']); ?>">
                                        <input type="submit" name="delete_upload" value="Delete" class="button button-small" onclick="return confirm('Are you sure you want to delete this upload?');">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}