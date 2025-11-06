/**
 * Form Handler JavaScript
 *
 * Handles AJAX form submissions for the plugin.
 */

jQuery(document).ready(function($) {

    // Secure form submission
    $('#wpsfd-secure-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('action', 'wpsfd_secure_submit');
        formData.append('wpsfd_secure_nonce', wpsfd_ajax.secure_nonce);

        $.ajax({
            url: wpsfd_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#wpsfd-secure-response').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    $('#wpsfd-secure-form')[0].reset();
                } else {
                    $('#wpsfd-secure-response').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $('#wpsfd-secure-response').html('<div class="notice notice-error"><p>AJAX error occurred.</p></div>');
            }
        });
    });

    // Insecure form submission
    $('#wpsfd-insecure-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('action', 'wpsfd_insecure_submit');
        // Note: No nonce added for insecure demo

        $.ajax({
            url: wpsfd_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#wpsfd-insecure-response').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    $('#wpsfd-insecure-form')[0].reset();
                } else {
                    $('#wpsfd-insecure-response').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $('#wpsfd-insecure-response').html('<div class="notice notice-error"><p>AJAX error occurred.</p></div>');
            }
        });
    });

    // File upload submission
    $('#wpsfd-upload-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('action', 'wpsfd_upload_file');
        formData.append('wpsfd_upload_nonce', wpsfd_ajax.upload_nonce);

        $.ajax({
            url: wpsfd_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#wpsfd-upload-response').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    $('#wpsfd-upload-form')[0].reset();
                } else {
                    $('#wpsfd-upload-response').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $('#wpsfd-upload-response').html('<div class="notice notice-error"><p>AJAX error occurred.</p></div>');
            }
        });
    });

});