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
                <li><a href="<?php echo admin_url('admin.php?page=wp-secure-forms-demo-insecure'); ?>">Insecure Form</a> - Shows dangerous practices like raw SQL and no validation (for educational purposes only).</li>
                <li><a href="<?php echo admin_url('admin.php?page=wp-secure-forms-demo-upload'); ?>">File Upload</a> - Safe file upload with type and size validation.</li>
                <li><a href="<?php echo admin_url('admin.php?page=wp-secure-forms-demo-submissions'); ?>">Submissions Viewer</a> - View all form submissions and uploaded files.</li>
            </ul>

            <h2>Demo Instructions</h2>
            <ol>
                <li>Try submitting both forms with normal data and malicious input (e.g., &lt;script&gt; tags, SQL injection attempts).</li>
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
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr><td colspan="6">No submissions yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?php echo esc_html($submission['id']); ?></td>
                                <td><?php echo esc_html($submission['name']); ?></td>
                                <td><?php echo esc_html($submission['email']); ?></td>
                                <td><?php echo esc_html($submission['message']); ?></td>
                                <td><?php echo $submission['is_secure'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo esc_html($submission['created_at']); ?></td>
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
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($uploads)): ?>
                        <tr><td colspan="7">No uploads yet.</td></tr>
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
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}