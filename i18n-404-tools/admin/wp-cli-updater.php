<?php
/**
 * WP-CLI Phar Manager for Admin
 * Place this file in your plugin's /admin/ directory.
 */

/**
 * Downloads or updates the WP-CLI Phar, and stores its version as a WP option.
 * Returns true on success, error message string on failure.
 */
function i18n_404_tools_wpcli_download_phar() {
    $bin_dir = plugin_dir_path(__FILE__) . 'bin/';
    $wp_cli_phar = $bin_dir . 'wp-cli.phar';
    $htaccess = $bin_dir . '.htaccess';

    // Ensure bin directory exists
    if (!file_exists($bin_dir)) {
        if (!wp_mkdir_p($bin_dir)) {
            return 'Could not create bin/ directory.';
        }
    }

    // Download latest Phar from official source
    $wp_cli_url = 'https://github.com/wp-cli/wp-cli/releases/latest/download/wp-cli.phar';
    $response = wp_remote_get($wp_cli_url, array('timeout' => 60));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return 'WP-CLI download failed: ' . (is_wp_error($response) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code($response));
    }

    $wp_cli_contents = wp_remote_retrieve_body($response);
    if (empty($wp_cli_contents)) {
        return 'WP-CLI download failed: Empty file received.';
    }

    if (file_put_contents($wp_cli_phar, $wp_cli_contents) === false) {
        return 'Failed to write wp-cli.phar file.';
    }
    @chmod($wp_cli_phar, 0755);

    // Try to get WP-CLI version string
    $php_path = PHP_BINARY;
    $cmd = escapeshellcmd("$php_path $wp_cli_phar --version");
    $output = shell_exec($cmd);

    if (empty($output) || !preg_match('/WP-CLI\s+version\s+([^\s]+)/i', $output, $matches)) {
        return 'Could not execute wp-cli.phar or retrieve version. Output: ' . esc_html($output);
    }
    $version = trim($matches[1]);

    // Store version in a WP option
    update_option('i18n_404_tools_wpcli_version', $version);

    // Secure bin/ with .htaccess (for Apache)
    $htaccess_contents = "Deny from all\n";
    file_put_contents($htaccess, $htaccess_contents);

    return true;
}

/**
 * Install or update WP-CLI on activation.
 */
function i18n_404_tools_wpcli_maybe_install() {
    $result = i18n_404_tools_wpcli_download_phar();
    if ($result !== true) {
        set_transient('i18n_404_tools_wpcli_download_error', $result, 60*5);
    }
}

/**
 * Admin notice if download failed.
 */
add_action('admin_notices', function() {
    if ($msg = get_transient('i18n_404_tools_wpcli_download_error')) {
        echo '<div class="notice notice-error"><p>' . esc_html($msg) . '</p></div>';
        delete_transient('i18n_404_tools_wpcli_download_error');
    }
});

/**
 * Helper: Retrieve the stored WP-CLI version (or blank if not set).
 */
function i18n_404_tools_wpcli_get_version() {
    return get_option('i18n_404_tools_wpcli_version', '');
}
