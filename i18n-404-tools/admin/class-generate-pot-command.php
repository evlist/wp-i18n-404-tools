<?php
/**
 * Generate .pot command class.
 *
 * @package I18n_404_Tools
 */

// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-i18n-command-base.php';

/**
 * Command for generating a .pot file for a plugin (with generic modal HTML output).
 */
class I18N_404_Generate_Pot_Command extends I18N_404_Command_Base {

	public function __construct( $plugin ) {
		parent::__construct( $plugin );

		// Load modal config and helpers for action attributes.
		require_once __DIR__ . '/modal-config.php';
		require_once __DIR__ . '/helpers.php';
	}

	/**
	 * Handle specific steps for .pot generation.
	 *
	 * @param string $step    The requested action: 'check' or 'generate'
	 * @param array  $request The request data, usually $_POST
	 * @return array          Response data (with 'html' key)
	 */
	public function run_step( $step, $request ) {
		// Step 1: Check if a .pot file exists and show confirmation.
		if ( 'check' === $step ) {
			if ( file_exists( $this->pot_path ) ) {
				return array(
					'html' => '<div class="i18n-modal-content">'
						. $this->generate_modal_header( __( 'Generate .pot', 'i18n-404-tools' ) )
						. '<p>' . esc_html__( 'A .pot file already exists. Overwrite?', 'i18n-404-tools' ) . '</p>'
						. $this->generate_action_button(
							__( 'Yes, overwrite', 'i18n-404-tools' ),
							'generate_pot',
							'generate',
							'button-primary',
							array( 'data-overwrite' => '1' )
						) . ' '
						. $this->generate_cancel_button( __( 'Cancel', 'i18n-404-tools' ) )
						. '</div>',
				);
			} else {
				return array(
					'html' => '<div class="i18n-modal-content">'
						. $this->generate_modal_header( __( 'Generate .pot', 'i18n-404-tools' ) )
						. '<p>' . esc_html__( 'No .pot file exists. Generate now?', 'i18n-404-tools' ) . '</p>'
						. $this->generate_action_button(
							__( 'Generate', 'i18n-404-tools' ),
							'generate_pot',
							'generate',
							'button-primary'
						) . ' '
						. $this->generate_cancel_button( __( 'Cancel', 'i18n-404-tools' ) )
						. '</div>',
				);
			}
		}

		// Step 2: Generate the .pot file and return output/result.
		if ( 'generate' === $step ) {

			$result = $this->run_wp_cli_command(
				'i18n make-pot',
				array(
					0        => $this->plugin_dir,
					1        => $this->pot_path,
					'domain' => $this->domain,
				)
			);

			$output = esc_html( trim( $result['stdout'] . "\n" . $result['stderr'] ) );
			if ( 0 === $result['exit_code'] && file_exists( $this->pot_path ) ) {
				$message = esc_html__( 'POT file generated successfully!', 'i18n-404-tools' );
			} else {
				$message = esc_html__( 'Failed to generate POT file.', 'i18n-404-tools' );
			}

			return array(
				'html' => '<div class="i18n-modal-content">'
					. $this->generate_modal_header( __( 'Generate .pot', 'i18n-404-tools' ) )
					. '<p><strong>' . $message . '</strong></p>'
					. '<div class="i18n-copy-wrap" style="display:flex;align-items:center;gap:5px;">'
					. '<button type="button" class="button i18n-copy-btn" title="' . esc_attr__( 'Copy output', 'i18n-404-tools' ) . '">'
					. '<span class="dashicons dashicons-clipboard"></span>'
					. '</button>'
					. '<pre class="i18n-modal-output" style="flex:1;overflow:auto;max-height:300px;background:#f6f7f7;padding:8px;border-radius:3px;">'
					. $output
					. '</pre>'
					. '</div>'
					. '<div style="margin-top:12px;">'
					. $this->generate_cancel_button( __( 'Close', 'i18n-404-tools' ) )
					. '</div>'
					. '</div>',
			);
		}

		// Fallback for unknown steps
		return array(
			'html' => '<div class="i18n-modal-content">'
				. '<p>' . esc_html__( 'Unknown step.', 'i18n-404-tools' ) . '</p>'
				. $this->generate_cancel_button( __( 'Close', 'i18n-404-tools' ) )
				. '</div>',
		);
	}
}
