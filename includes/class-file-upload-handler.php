<?php
/**
 * File Upload Handler Class
 *
 * Demonstrates safe file upload with validation.
 */

namespace WPSFD;

class File_Upload_Handler {

    /**
     * Display the file upload form.
     */
    public static function display_form() {
        ?>
        <div class="wrap">
            <h1>Safe File Upload Demo</h1>
            <p>This form demonstrates safe file upload with file type, size, and extension validation.</p>
            <p>Allowed file types: JPG, PNG, PDF. Maximum size: 2MB.</p>

            <form id="wpsfd-upload-form" enctype="multipart/form-data" method="post">
                <?php wp_nonce_field('wpsfd_upload_file', 'wpsfd_upload_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="file">Select File</label></th>
                        <td><input type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Upload File">
                </p>
            </form>

            <div id="wpsfd-upload-response"></div>
        </div>
        <?php
    }

    /**
     * Handle AJAX file upload.
     */
    public static function handle_ajax_upload() {
        // Check nonce
        if (!wp_verify_nonce($_POST['wpsfd_upload_nonce'], 'wpsfd_upload_file')) {
            wp_send_json_error(['message' => 'Security check failed.']);
        }

        // Check if file was uploaded
        if (empty($_FILES['file'])) {
            wp_send_json_error(['message' => 'No file uploaded.']);
        }

        $file = $_FILES['file'];

        // Validate file size (2MB max)
        $max_size = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $max_size) {
            wp_send_json_error(['message' => 'File size exceeds 2MB limit.']);
        }

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(['message' => 'Invalid file type. Only JPG, PNG, and PDF are allowed.']);
        }

        // Validate file extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            wp_send_json_error(['message' => 'Invalid file extension.']);
        }

        // Use wp_handle_upload for safe upload
        $upload_overrides = [
            'test_form' => false,
            'upload_error_handler' => function($file, $message) {
                wp_send_json_error(['message' => 'Upload failed: ' . $message]);
            }
        ];

        $uploaded_file = wp_handle_upload($file, $upload_overrides);

        if (isset($uploaded_file['error'])) {
            wp_send_json_error(['message' => 'Upload failed: ' . $uploaded_file['error']]);
        }

        // Save file info to database
        $data = [
            'file_name' => sanitize_file_name($file['name']),
            'file_path' => $uploaded_file['file'],
            'file_url' => $uploaded_file['url'],
            'file_size' => $file['size'],
            'file_type' => $file['type'],
        ];

        $result = Database_Manager::insert_upload($data);

        if ($result) {
            wp_send_json_success(['message' => 'File uploaded successfully!']);
        } else {
            wp_send_json_error(['message' => 'Failed to save file information.']);
        }
    }
}