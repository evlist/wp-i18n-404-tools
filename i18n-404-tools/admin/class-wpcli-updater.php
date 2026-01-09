<?php
/**
 * WP-CLI Updater Class.
 *
 * @package I18n_404_Tools
 */

/*
 * SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

if ( ! class_exists( 'I18n_404_Tools_WPCLI_Updater' ) ) {

	/**
	 * WP-CLI Phar Updater Class for i18n-404-tools.
	 */
	class I18n_404_Tools_WPCLI_Updater {
		const BIN_DIR          = 'bin/';
		const PHAR_FILE        = 'wp-cli.phar';
		const VERSION_OPTION   = 'i18n_404_tools_wpcli_version';
		const NOTICE_TRANSIENT = 'i18n_404_tools_wpcli_update_notice';
		const PHAR_URL         = 'https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar';
		/**
		 * Get the full path to the WP-CLI phar file.
		 *
		 * @return string Path to wp-cli.phar.
		 */     public static function get_phar_path() {
			return plugin_dir_path( __FILE__ ) . self::BIN_DIR . self::PHAR_FILE;
}

		/**
		 * Private: Download and check version. Returns true or error string.
		 */
private function download_phar() {
	$bin_dir     = plugin_dir_path( __FILE__ ) . self::BIN_DIR;
	$wp_cli_phar = $bin_dir . self::PHAR_FILE;
	$htaccess    = $bin_dir . '.htaccess';

	// Ensure bin directory exists.
	if ( ! file_exists( $bin_dir ) ) {
		if ( ! wp_mkdir_p( $bin_dir ) ) {
			return 'Could not create bin/ directory.';
		}
	}

	// Download latest Phar from official source.
	$response = wp_remote_get( self::PHAR_URL, array( 'timeout' => 60 ) );
	if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
		return 'WP-CLI download failed: ' . ( is_wp_error( $response ) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $response ) );
	}
	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		return 'WP-CLI download failed: Empty file received.';
	}
	if ( file_put_contents( $wp_cli_phar, $body ) === false ) {
		return 'Failed to write wp-cli.phar file.';
	}
	@chmod( $wp_cli_phar, 0755 );

	// Get version.
	$php_path = PHP_BINARY;
	$cmd      = escapeshellcmd( "$php_path $wp_cli_phar --version" );
	$output   = shell_exec( $cmd );

	if (
			empty( $output ) ||
			! preg_match( '/WP-CLI(?:\s+version)?\s+([0-9.]+)/i', $output, $matches )
		) {
		return 'Could not execute wp-cli.phar or retrieve version. Output: ' . esc_html( $output );
	}
	$version = trim( $matches[1] );
	update_option( self::VERSION_OPTION, $version );

	// Secure bin/ for Apache.
	file_put_contents( $htaccess, "Deny from all\n" );

	return true;
}

		/**
		 * Public: Download/update the phar and set admin notice for result.
		 *
		 * @param string|null $success_msg Optional success message to display.
		 */
public function download_phar_with_notice( $success_msg = null ) {
	$result = $this->download_phar();
	if ( true === $result ) {
		set_transient(
			self::NOTICE_TRANSIENT,
			array(
				'type'    => 'success',
				'message' => $success_msg ? $success_msg : __( 'WP-CLI was successfully installed.', 'i18n-404-tools' ),
			),
			30
		);
	} else {
		set_transient(
			self::NOTICE_TRANSIENT,
			array(
				'type'    => 'error',
				'message' => $result,
			),
			30
		);
	}
	return $result;
}

		/**
		 * Show admin notice if set
		 */
public static function show_admin_notice() {
	$notice = get_transient( self::NOTICE_TRANSIENT );
	if ( $notice ) {
		$class = ( 'success' === $notice['type'] ) ? 'notice-success' : 'notice-error';
		echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $notice['message'] ) . '</p></div>';
		delete_transient( self::NOTICE_TRANSIENT );
	}
}

		/**
		 * Get stored version of WP-CLI
		 */
public static function get_version() {
	return get_option( self::VERSION_OPTION, '' );
}
	}
}

// Instantiate the updater.
if ( ! isset( $GLOBALS['i18n_404_tools_wpcli_updater'] ) ) {
	$GLOBALS['i18n_404_tools_wpcli_updater'] = new I18n_404_Tools_WPCLI_Updater();
}

// Show admin notices.
add_action( 'admin_notices', array( 'I18n_404_Tools_WPCLI_Updater', 'show_admin_notice' ) );
