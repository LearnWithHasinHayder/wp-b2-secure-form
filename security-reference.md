# WordPress Security Reference Guide

This comprehensive guide covers essential WordPress security functions, best practices, and implementation examples for building secure plugins and themes.

## Table of Contents
- [Sanitization Functions](#sanitization-functions)
- [Nonce Functions](#nonce-functions)
- [Nonce Verification Functions](#nonce-verification-functions)
- [Validation Functions](#validation-functions)
- [Security Best Practices](#security-best-practices)
- [Database Security](#database-security)
- [File Upload Security](#file-upload-security)
- [User Permissions](#user-permissions)
- [Common Security Pitfalls](#common-security-pitfalls)

## Sanitization Functions

Sanitization ensures user input is safe for storage and display. Always sanitize data before using it.

### sanitize_text_field()
Cleans a string for safe use as plain text.

```php
$name = sanitize_text_field($_POST['name']);
// Removes HTML tags, line breaks, extra whitespace
```

### sanitize_email()
Validates and sanitizes an email address.

```php
$email = sanitize_email($_POST['email']);
// Converts to lowercase, removes invalid characters
```

### sanitize_textarea_field()
Sanitizes content for a textarea, allowing line breaks.

```php
$message = sanitize_textarea_field($_POST['message']);
// Allows line breaks but removes HTML
```

### sanitize_url()
Validates and sanitizes a URL.

```php
$url = sanitize_url($_POST['website']);
// Ensures proper URL format
```

### sanitize_key()
Sanitizes a string key name (alphanumeric, dashes, underscores).

```php
$key = sanitize_key($_POST['option_key']);
// Perfect for database keys and option names
```

### sanitize_title()
Sanitizes a title string for use in URLs or filenames.

```php
$title = sanitize_title($_POST['post_title']);
// Creates URL-friendly slugs
```

### sanitize_html_class()
Sanitizes a string for use as an HTML class name.

```php
$class = sanitize_html_class($_POST['css_class']);
// Removes spaces and special characters
```

### sanitize_file_name()
Sanitizes a filename, replacing special characters.

```php
$filename = sanitize_file_name($_FILES['file']['name']);
// Safe for file operations
```

### wp_kses()
Allows only specified HTML tags and attributes.

```php
$allowed_tags = array(
    'a' => array('href' => array(), 'title' => array()),
    'br' => array(),
    'em' => array(),
    'strong' => array(),
);
$content = wp_kses($_POST['content'], $allowed_tags);
```

### wp_kses_post()
Allows HTML tags permitted in post content.

```php
$content = wp_kses_post($_POST['post_content']);
// Safe for post content with HTML
```

### esc_html()
Escapes HTML entities for safe display.

```php
echo '<h1>' . esc_html($title) . '</h1>';
// Prevents XSS in HTML output
```

### esc_attr()
Escapes HTML attributes.

```php
echo '<input value="' . esc_attr($value) . '">';
// Safe for HTML attributes
```

### esc_url()
Escapes URLs for safe use in HTML.

```php
echo '<a href="' . esc_url($url) . '">Link</a>';
// Safe for href attributes
```

### esc_js()
Escapes strings for safe use in JavaScript.

```php
echo '<script>var data = "' . esc_js($data) . '";</script>';
// Safe for inline JavaScript
```

## Nonce Functions

Nonces prevent Cross-Site Request Forgery (CSRF) attacks by ensuring requests come from legitimate sources.

### wp_create_nonce()
Creates a cryptographic token tied to the current user and action.

```php
$nonce = wp_create_nonce('my_action');
// Returns a unique token
```

### wp_nonce_field()
Outputs a hidden nonce field in forms.

```php
wp_nonce_field('my_action', 'my_nonce_field');
// Adds: <input type="hidden" name="my_nonce_field" value="abc123">
```

### wp_nonce_url()
Adds a nonce to a URL.

```php
$url = wp_nonce_url($base_url, 'my_action');
// Adds ?_wpnonce=abc123 to the URL
```

### wp_nonce_ays()
Displays "Are you sure?" dialog for destructive actions.

```php
wp_nonce_ays('delete_item');
// Shows confirmation dialog
```

## Nonce Verification Functions

Always verify nonces before processing form submissions or actions.

### wp_verify_nonce()
Verifies a nonce value.

```php
if (!wp_verify_nonce($_POST['my_nonce_field'], 'my_action')) {
    wp_die('Security check failed');
}
// Returns 1 if valid, 2 if valid but from different session, false if invalid
```

### check_admin_referer()
Verifies nonce in admin screens.

```php
check_admin_referer('my_action');
// Dies if nonce is invalid
```

### check_ajax_referer()
Verifies nonce in AJAX requests.

```php
check_ajax_referer('my_action', 'nonce_field');
// Dies if nonce is invalid, returns true if valid
```

## Validation Functions

Validation ensures data meets expected criteria before processing.

### is_email()
Validates an email address format.

```php
if (!is_email($email)) {
    $errors[] = 'Invalid email address';
}
```

### validate_file()
Validates a file path for security.

```php
$result = validate_file($filepath);
// Returns 0 if valid, 1 if file not in allowed directory, 2 if above basedir
```

### wp_check_filetype()
Guesses the file type based on extension.

```php
$filetype = wp_check_filetype($filename);
// Returns array with 'type' (MIME type) and 'ext' (extension)
```

### wp_check_filetype_and_ext()
Validates file type and extension match.

```php
$checked = wp_check_filetype_and_ext($filepath, $filename);
// Returns array with proper MIME type and extension
```

### current_user_can()
Checks if current user has a capability.

```php
if (!current_user_can('edit_posts')) {
    wp_die('Insufficient permissions');
}
```

## Security Best Practices

### 1. Always Sanitize User Input
```php
// WRONG - Direct use of user input
$name = $_POST['name'];
echo $name;

// RIGHT - Sanitize first
$name = sanitize_text_field($_POST['name']);
echo esc_html($name);
```

### 2. Use Nonces for Form Security
```php
// In your form
<form method="post">
    <?php wp_nonce_field('save_settings', 'settings_nonce'); ?>
    <input type="text" name="setting_value">
    <input type="submit" value="Save">
</form>

// In your processing
if (isset($_POST['settings_nonce']) && wp_verify_nonce($_POST['settings_nonce'], 'save_settings')) {
    // Process form
    $value = sanitize_text_field($_POST['setting_value']);
    update_option('my_setting', $value);
}
```

### 3. Validate Data Types
```php
// Validate integers
$id = intval($_GET['id']);
if ($id <= 0) {
    wp_die('Invalid ID');
}

// Validate arrays
if (!is_array($_POST['items'])) {
    wp_die('Invalid data');
}
```

### 4. Use Prepared Statements for Database Queries
```php
// WRONG - Direct interpolation (SQL injection vulnerable)
$user_id = $_GET['user_id'];
$query = "SELECT * FROM {$wpdb->users} WHERE ID = $user_id";

// RIGHT - Prepared statement
$user_id = intval($_GET['user_id']);
$user = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->users} WHERE ID = %d",
    $user_id
));
```

### 5. Escape Output Appropriately
```php
// HTML output
echo '<div class="' . esc_attr($class) . '">' . esc_html($content) . '</div>';

// URLs
echo '<a href="' . esc_url($url) . '">' . esc_html($text) . '</a>';

// JavaScript
wp_localize_script('my-script', 'myData', array(
    'message' => esc_js($message)
));
```

### 6. Secure File Uploads
```php
if (!function_exists('wp_handle_upload')) {
    require_once(ABSPATH . 'wp-admin/includes/file.php');
}

$uploadedfile = $_FILES['file'];
$upload_overrides = array('test_form' => false);

$movefile = wp_handle_upload($uploadedfile, $upload_overrides);

if ($movefile && !isset($movefile['error'])) {
    // File uploaded successfully
    $file_url = $movefile['url'];
    $file_path = $movefile['file'];
} else {
    // Handle error
    $error = $movefile['error'];
}
```

### 7. Check User Permissions
```php
// Check basic capability
if (!current_user_can('edit_posts')) {
    wp_die('You do not have permission to edit posts');
}

// Check for specific post
if (!current_user_can('edit_post', $post_id)) {
    wp_die('You cannot edit this post');
}

// Check custom capability
if (!current_user_can('manage_my_plugin')) {
    wp_die('Access denied');
}
```

### 8. Use Safe Redirects
```php
// WRONG - Direct redirect
wp_redirect($_GET['redirect_to']);

// RIGHT - Safe redirect
$redirect_url = wp_validate_redirect($_GET['redirect_to'], home_url());
wp_safe_redirect($redirect_url);
exit;
```

### 9. Avoid Direct Database Queries When Possible
```php
// Use WordPress functions instead of direct queries
update_option('my_option', $value);
get_option('my_option');

// Use post meta functions
update_post_meta($post_id, 'my_key', $value);
get_post_meta($post_id, 'my_key', true);
```

### 10. Secure AJAX Endpoints
```php
// Register AJAX handler
add_action('wp_ajax_my_action', 'my_ajax_handler');

// Handler function
function my_ajax_handler() {
    // Verify nonce
    check_ajax_referer('my_nonce_action', 'nonce');

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    // Sanitize input
    $data = sanitize_text_field($_POST['data']);

    // Process and respond
    wp_send_json_success(array('result' => 'Success'));
}
```

## Database Security

### Use $wpdb->prepare() for All Queries
```php
// SELECT query
$user = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->users} WHERE user_email = %s",
    $email
));

// INSERT query
$wpdb->insert(
    $table_name,
    array(
        'name' => $name,
        'email' => $email,
    ),
    array('%s', '%s')
);

// UPDATE query
$wpdb->update(
    $table_name,
    array('status' => 'active'),
    array('id' => $id),
    array('%s'),
    array('%d')
);
```

### Table Name Prefixing
```php
// Always use table prefixes
$table_name = $wpdb->prefix . 'my_plugin_data';

// Correct
$query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id);

// Wrong - hard-coded table name
$query = $wpdb->prepare("SELECT * FROM wp_my_plugin_data WHERE id = %d", $id);
```

## File Upload Security

### Validate File Types
```php
$allowed_types = array('image/jpeg', 'image/png', 'application/pdf');

$filetype = wp_check_filetype($_FILES['file']['name']);
if (!in_array($filetype['type'], $allowed_types)) {
    wp_die('Invalid file type');
}
```

### Check File Size
```php
$max_size = 2 * 1024 * 1024; // 2MB
if ($_FILES['file']['size'] > $max_size) {
    wp_die('File too large');
}
```

### Use WordPress Upload Functions
```php
// Instead of move_uploaded_file()
$upload = wp_handle_upload($_FILES['file'], array('test_form' => false));
if (isset($upload['error'])) {
    wp_die($upload['error']);
}
```

## User Permissions

### Capability Checking
```php
// Basic capabilities
current_user_can('read')           // Can read posts
current_user_can('edit_posts')     // Can edit own posts
current_user_can('publish_posts')  // Can publish posts
current_user_can('delete_posts')   // Can delete posts

// Meta capabilities (context-aware)
current_user_can('edit_post', $post_id)      // Can edit specific post
current_user_can('delete_post', $post_id)    // Can delete specific post
current_user_can('read_post', $post_id)      // Can read specific post
```

### Role-Based Access
```php
$user = wp_get_current_user();

// Check user role
if (in_array('administrator', $user->roles)) {
    // User is admin
}

// Check multiple roles
$allowed_roles = array('administrator', 'editor');
if (array_intersect($allowed_roles, $user->roles)) {
    // User has allowed role
}
```

## Common Security Pitfalls

### 1. Not Sanitizing Output
```php
// VULNERABLE
echo $_POST['user_input'];

// SECURE
echo esc_html($_POST['user_input']);
```

### 2. SQL Injection
```php
// VULNERABLE
$query = "SELECT * FROM table WHERE id = " . $_GET['id'];

// SECURE
$query = $wpdb->prepare("SELECT * FROM table WHERE id = %d", $_GET['id']);
```

### 3. Cross-Site Scripting (XSS)
```php
// VULNERABLE
echo "<div>$user_content</div>";

// SECURE
echo "<div>" . esc_html($user_content) . "</div>";
```

### 4. Missing Nonce Verification
```php
// VULNERABLE
if (isset($_POST['submit'])) {
    update_option('setting', $_POST['value']);
}

// SECURE
if (isset($_POST['submit']) && wp_verify_nonce($_POST['nonce'], 'update_setting')) {
    update_option('setting', sanitize_text_field($_POST['value']));
}
```

### 5. Directory Traversal
```php
// VULNERABLE
include $_GET['file'] . '.php';

// SECURE
$allowed_files = array('file1', 'file2', 'file3');
if (in_array($_GET['file'], $allowed_files)) {
    include $_GET['file'] . '.php';
}
```

### 6. Unvalidated Redirects
```php
// VULNERABLE
wp_redirect($_GET['url']);

// SECURE
wp_safe_redirect(wp_validate_redirect($_GET['url']));
```

### 7. Insufficient File Validation
```php
// VULNERABLE
move_uploaded_file($_FILES['file']['tmp_name'], '/uploads/' . $_FILES['file']['name']);

// SECURE
$upload = wp_handle_upload($_FILES['file'], array('test_form' => false));
```

## Quick Reference

### Input Processing Flow
1. **Check request method**: `if ($_SERVER['REQUEST_METHOD'] === 'POST')`
2. **Verify nonce**: `wp_verify_nonce($_POST['nonce'], 'action')`
3. **Check permissions**: `current_user_can('capability')`
4. **Sanitize input**: `sanitize_text_field()`, `sanitize_email()`, etc.
5. **Validate data**: `is_email()`, custom validation
6. **Process data**: Use prepared statements, WordPress functions
7. **Escape output**: `esc_html()`, `esc_attr()`, `esc_url()`

### Essential Security Functions
- `wp_verify_nonce()` - Verify CSRF tokens
- `current_user_can()` - Check permissions
- `sanitize_*()` functions - Clean user input
- `esc_*()` functions - Safe output
- `$wpdb->prepare()` - Safe database queries
- `wp_handle_upload()` - Secure file uploads

Remember: Security is not a one-time implementation but an ongoing process. Always keep WordPress core, plugins, and themes updated, and regularly audit your code for vulnerabilities.