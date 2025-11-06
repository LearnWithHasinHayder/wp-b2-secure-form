<?php
/**
 * Database Manager Class
 *
 * Handles database table creation and CRUD operations.
 */

namespace WPSFD;

class Database_Manager {

    /**
     * Table name for form submissions.
     */
    const SUBMISSIONS_TABLE = 'wp_secure_form_submissions';

    /**
     * Table name for file uploads.
     */
    const UPLOADS_TABLE = 'wp_secure_form_uploads';

    /**
     * Create database tables on plugin activation.
     */
    public static function create_tables() {
        global $wpdb;

        if (!function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }

        $charset_collate = $wpdb->get_charset_collate();

        // Submissions table
        $submissions_table = $wpdb->prefix . self::SUBMISSIONS_TABLE;
        $sql_submissions = "CREATE TABLE $submissions_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            message text NOT NULL,
            is_secure tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Uploads table
        $uploads_table = $wpdb->prefix . self::UPLOADS_TABLE;
        $sql_uploads = "CREATE TABLE $uploads_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            file_name varchar(255) NOT NULL,
            file_path varchar(500) NOT NULL,
            file_url varchar(500) NOT NULL,
            file_size int(11) NOT NULL,
            file_type varchar(100) NOT NULL,
            uploaded_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql_submissions);
        dbDelta($sql_uploads);
    }

    /**
     * Insert a form submission.
     *
     * @param array $data Submission data.
     * @param bool $is_secure Whether it's a secure submission.
     * @return bool|int False on failure, inserted ID on success.
     */
    public static function insert_submission($data, $is_secure = false) {
        global $wpdb;

        $table = $wpdb->prefix . self::SUBMISSIONS_TABLE;

        $result = $wpdb->insert(
            $table,
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'message' => $data['message'],
                'is_secure' => $is_secure ? 1 : 0,
            ],
            ['%s', '%s', '%s', '%d']
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get all submissions.
     *
     * @return array List of submissions.
     */
    public static function get_all_submissions() {
        global $wpdb;

        $table = $wpdb->prefix . self::SUBMISSIONS_TABLE;

        return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);
    }

    /**
     * Insert an upload record.
     *
     * @param array $data Upload data.
     * @return bool|int False on failure, inserted ID on success.
     */
    public static function insert_upload($data) {
        global $wpdb;

        $table = $wpdb->prefix . self::UPLOADS_TABLE;

        $result = $wpdb->insert(
            $table,
            [
                'file_name' => $data['file_name'],
                'file_path' => $data['file_path'],
                'file_url' => $data['file_url'],
                'file_size' => $data['file_size'],
                'file_type' => $data['file_type'],
            ],
            ['%s', '%s', '%s', '%d', '%s']
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get all uploads.
     *
     * @return array List of uploads.
     */
    public static function get_all_uploads() {
        global $wpdb;

        $table = $wpdb->prefix . self::UPLOADS_TABLE;

        return $wpdb->get_results("SELECT * FROM $table ORDER BY uploaded_at DESC", ARRAY_A);
    }
}