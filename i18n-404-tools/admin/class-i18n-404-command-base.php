<?php
/**
 * I18n command base class.
 *
 * @package I18n_404_Tools
 */

/*
 * SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Make sure the WPCLI updater class is loaded.
require_once __DIR__ . '/class-i18n-404-tools-wpcli-updater.php';

/**
 * Abstract base class for i18n-404-tools commands.
 */
abstract class I18N_404_Command_Base {

	/**
	 * Plugin file slug.
	 *
	 * @var string Plugin file slug (e.g., hello-dolly/hello.php)
	 */
	protected $plugin;

	/**
	 * Full path to the plugin main file.
	 *
	 * @var string Full path to the plugin main file
	 */
	protected $plugin_path;

	/**
	 * Directory containing the plugin.
	 *
	 * @var string Directory containing the plugin
	 */
	protected $plugin_dir;

	/**
	 * Directory for languages.
	 *
	 * @var string Directory for languages/
	 */
	protected $languages_dir;

	/**
	 * Text domain.
	 *
	 * @var string Text domain (guessed from plugin main file)
	 */
	protected $domain;

	/**
	 * Full path to the .pot file.
	 *
	 * @var string Full path to the .pot file
	 */
	protected $pot_path;

	/**
	 * Set up context for the command.
	 *
	 * @param string $plugin The plugin file slug (e.g. hello-dolly/hello.php).
	 * @throws Exception If plugin is missing or paths are invalid.
	 */
	public function __construct( $plugin ) {
		$this->plugin      = sanitize_text_field( $plugin );
		$this->plugin_path = WP_PLUGIN_DIR . '/' . $this->plugin;

		if ( ! file_exists( $this->plugin_path ) ) {
			throw new Exception( esc_html__( 'Plugin not found.', 'i18n-404-tools' ) );
		}

		$this->plugin_dir    = dirname( $this->plugin_path );
		$this->languages_dir = $this->plugin_dir . '/languages';

		if ( ! is_dir( $this->languages_dir ) ) {
			// Attempt to create the languages dir using WordPress helper.
			// Using wp_mkdir_p avoids error silencing and respects WP standards.
			wp_mkdir_p( $this->languages_dir );
		}

		$this->domain   = basename( $this->plugin, '.php' );
		$this->pot_path = $this->languages_dir . '/' . $this->domain . '.pot';
	}

	/**
	 * Entrypoint for command execution.
	 * Must be implemented by child classes.
	 *
	 * @param string $step    The sub-action/step to perform (e.g. 'check', 'generate').
	 * @param array  $request Full request (usually $_POST).
	 * @return array          Result data for Ajax response.
	 */
	abstract public function run_step( $step, $request );

	/**     * Generate modal header with logo.
	 *
	 * @param string $title Optional title text.
	 * @return string       HTML for the header with logo.
	 */
	protected function generate_modal_header( $title = '' ) {
		// Build logo URL using the plugin directory.
		$plugin_dir = dirname( dirname( __DIR__ ) ); // Go up to /i18n-404-tools/.
		$logo_path  = $plugin_dir . '/i18n-404-tools/admin/images/logo.svg';
		$logo_url   = esc_url( plugins_url( 'admin/images/logo.svg', $plugin_dir . '/i18n-404-tools/i18n-404-tools.php' ) );

		$header = '<div class="i18n-modal-header" style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">'
			. '<img src="' . $logo_url . '" alt="i18n Tools" style="width:48px;height:48px;" />';

		if ( ! empty( $title ) ) {
			$header .= '<h3 style="margin:0;font-size:18px;">' . esc_html( $title ) . '</h3>';
		}

		$header .= '</div>';
		return $header;
	}

	/**     * Generate a cancel/close button with proper classes from modal-config.php.
	 *
	 * @param string $label              Button label text.
	 * @param string $additional_classes Optional additional CSS classes.
	 * @return string                    HTML for the cancel button.
	 */
	protected function generate_cancel_button( $label, $additional_classes = '' ) {
		global $i18n_404_tools_modal_config;

		// Ensure modal config is loaded.
		if ( ! isset( $i18n_404_tools_modal_config ) ) {
			require_once __DIR__ . '/modal-config.php';
		}

		$classes = array( 'button', $i18n_404_tools_modal_config['close_class'] );
		if ( ! empty( $additional_classes ) ) {
			$classes[] = $additional_classes;
		}

		return '<button type="button" class="' . esc_attr( implode( ' ', $classes ) ) . '">'
			. esc_html( $label )
			. '</button>';
	}

	/**
	 * Generate an action button with proper classes and data attributes from modal-config.php.
	 *
	 * @param string $label              Button label text.
	 * @param string $command            Command name for the action.
	 * @param string $step               Step name for the action.
	 * @param string $additional_classes Optional additional CSS classes.
	 * @param array  $additional_attrs   Optional additional data attributes.
	 * @return string                    HTML for the action button.
	 */
	protected function generate_action_button( $label, $command, $step, $additional_classes = '', $additional_attrs = array() ) {
		global $i18n_404_tools_modal_config;

		// Ensure modal config and helpers are loaded.
		if ( ! isset( $i18n_404_tools_modal_config ) ) {
			require_once __DIR__ . '/modal-config.php';
		}
		if ( ! function_exists( 'i18n_404_tools_action_attrs' ) ) {
			require_once __DIR__ . '/helpers.php';
		}

		$base_attrs = i18n_404_tools_action_attrs( $command, $this->plugin, $step, $additional_classes );

		$extra_attrs = '';
		foreach ( $additional_attrs as $attr_name => $attr_value ) {
			$extra_attrs .= ' ' . esc_attr( $attr_name ) . '="' . esc_attr( $attr_value ) . '"';
		}

		return '<button type="button" ' . $base_attrs . $extra_attrs . '>'
			. esc_html( $label )
			. '</button>';
	}

	/**
	 * Run a WP-CLI command with flexible arguments.
	 * - Numeric keys are positional arguments.
	 * - String keys with null values become flags (--foo).
	 * - String keys with values become options (--foo="bar").
	 *
	 * @param string $subcommand E.g., 'i18n make-pot'.
	 * @param array  $args       Command arguments and flags.
	 * @param string $cwd        Optional working directory.
	 * @return array             ['stdout' => ..., 'stderr' => ..., 'exit_code' => ...].
	 */
	protected function run_wp_cli_command( $subcommand, array $args = array(), $cwd = null ) {
		// Use the predefined PHP binary if available, or use system default.
		// PHP_BINARY is not available in web context (PHP-FPM), so use system default.
		$wp_cli_php = defined( 'WP_CLI_PHP_BINARY' ) ? constant( 'WP_CLI_PHP_BINARY' ) : '';
		$php_path   = $wp_cli_php ? $wp_cli_php : '/usr/bin/php';

		// Get the WP-CLI phar path from updater class.
		$wp_cli_phar = I18n_404_Tools_WPCLI_Updater::get_phar_path();

		$cmd_parts = array(
			escapeshellarg( $php_path ),
			escapeshellarg( $wp_cli_phar ),
		);

		// Add the subcommand (e.g. 'i18n make-pot' -> ['i18n', 'make-pot']).
		foreach ( explode( ' ', $subcommand ) as $part ) {
			$cmd_parts[] = escapeshellarg( $part );
		}

		// Add arguments and flags.
		foreach ( $args as $key => $value ) {
			if ( is_int( $key ) ) {
				// Positional argument.
				$cmd_parts[] = escapeshellarg( $value );
			} elseif ( is_null( $value ) ) {
				// Option or flag.
				$cmd_parts[] = '--' . $key;
			} else {
				// Option with value.
				$cmd_parts[] = '--' . $key . '=' . escapeshellarg( $value );
			}
		}

		$cmd = implode( ' ', $cmd_parts );

		// Ensure we use ABSPATH as working directory for WP-CLI context.
		if ( ! $cwd ) {
			$cwd = ABSPATH;
		}

		$descriptorspec = array(
			1 => array( 'pipe', 'w' ), // stdout.
			2 => array( 'pipe', 'w' ), // stderr.
		);

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_proc_open, Generic.PHP.ForbiddenFunctions.Found -- Required for WP-CLI execution.
		$process = proc_open( $cmd, $descriptorspec, $pipes, $cwd );

		if ( is_resource( $process ) ) {
			$stdout = stream_get_contents( $pipes[1] );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Required for pipe cleanup.
			fclose( $pipes[1] );
			$stderr = stream_get_contents( $pipes[2] );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Required for pipe cleanup.
			fclose( $pipes[2] );
			$exit_code = proc_close( $process );

			return array(
				'stdout'    => $stdout,
				'stderr'    => $stderr,
				'exit_code' => $exit_code,
			);
		} else {
			return array(
				'stdout'    => '',
				'stderr'    => 'Could not open process.',
				'exit_code' => 1,
			);
		}
	}
}
