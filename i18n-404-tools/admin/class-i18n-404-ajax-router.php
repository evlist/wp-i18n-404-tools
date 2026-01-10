<?php
/**
 * SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * @package I18n_404_Tools
 */

/**
 * AJAX Router for WP i18n 404 Tools.
 *
 * @package I18n_404_Tools
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Routes AJAX commands to the correct handler classes.
 */
class I18N_404_Ajax_Router {

	/**
	 * Map commands to their handler class and file.
	 *
	 * @var array<string, array{0:string,1:string}>
	 */
	protected $commands = array(
		// Example mapping: command => [ class name, file name ].
		'generate_pot'  => array( 'I18N_404_Generate_Pot_Command', 'class-i18n-404-generate-pot-command.php' ),
		'generate_json' => array( 'I18N_404_Generate_JSON_Command', 'class-i18n-404-generate-json-command.php' ),
	);

	/**
	 * Hook Ajax handler.
	 */
	public function __construct() {
		add_action( 'wp_ajax_i18n_404_tools_command', array( $this, 'handle_ajax' ) );
	}

	/**
	 * Handle Ajax command requests.
	 */
	public function handle_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized request.', 'i18n-404-tools' ) ), 403 );
		}

		check_ajax_referer( 'i18n_404_tools_action' );

		$plugin_slug = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';
		$command     = isset( $_POST['command'] ) ? sanitize_key( wp_unslash( $_POST['command'] ) ) : '';
		$step        = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		$request     = wp_unslash( $_POST );

		if ( empty( $plugin_slug ) || empty( $command ) || empty( $step ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing required parameters.', 'i18n-404-tools' ) ) );
		}

		if ( ! isset( $this->commands[ $command ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown command.', 'i18n-404-tools' ) ) );
		}
		list( $class_name, $file_name ) = $this->commands[ $command ];

		if ( ! class_exists( $class_name ) ) {
			$file_path = __DIR__ . '/' . $file_name;
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			} else {
				wp_send_json_error( array( 'message' => __( 'Command file not found: ', 'i18n-404-tools' ) . $file_name ) );
			}
		}

		if ( ! class_exists( $class_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Command class not found after loading file.', 'i18n-404-tools' ) ) );
		}

		try {
			$handler = new $class_name( $plugin_slug );
			$result  = $handler->run_step( $step, $request );

			if ( isset( $result['error'] ) ) {
				wp_send_json_error( $result );
			} else {
				wp_send_json_success( $result );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		} catch ( Throwable $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
		wp_die();
	}
}
