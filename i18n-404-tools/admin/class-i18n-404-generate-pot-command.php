<?php
/**
 * Generate .pot command class.
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

require_once __DIR__ . '/class-i18n-404-command-base.php';

/**
 * Command for generating a .pot file for a plugin (with generic modal HTML output).
 */
class I18N_404_Generate_Pot_Command extends I18N_404_Command_Base {

	/**
	 * Set up the POT command.
	 *
	 * @param string $plugin Plugin file slug.
	 */
	public function __construct( $plugin ) {
		parent::__construct( $plugin );

		// Load modal config and helpers for action attributes.
		require_once __DIR__ . '/modal-config.php';
		require_once __DIR__ . '/helpers.php';
	}

	/**
	 * Handle specific steps for .pot generation.
	 *
	 * @param string $step    The requested action: 'check' or 'generate'.
	 * @param array  $request The request data, usually $_POST.
	 * @return array          Response data (with 'html' key).
	 */
	public function run_step( $step, $request ) {
		// Step 1: Always ask for confirmation before generation.
		if ( 'check' === $step ) {
			if ( file_exists( $this->pot_path ) ) {
				$msg = esc_html__( 'A .pot file already exists. Overwrite?', 'i18n-404-tools' );
				$btn = $this->generate_action_button(
					__( 'Yes, overwrite', 'i18n-404-tools' ),
					'generate_pot',
					'generate',
					'button-primary',
					array( 'data-overwrite' => '1' )
				);
			} else {
				$msg = esc_html__( 'No .pot file exists. Generate now?', 'i18n-404-tools' );
				$btn = $this->generate_action_button(
					__( 'Generate', 'i18n-404-tools' ),
					'generate_pot',
					'generate',
					'button-primary'
				);
			}
			return array(
				'html' => '<div class="i18n-modal-content">'
					. $this->generate_modal_header( __( 'Generate .pot', 'i18n-404-tools' ) )
					. '<p>' . $msg . '</p>'
					. $btn . ' '
					. $this->generate_cancel_button( __( 'Cancel', 'i18n-404-tools' ) )
					. '</div>',
			);
		}

		// Step 2: Génération du .pot
		if ( 'generate' === $step ) {
			$result = $this->extractor->generate_pot( $this->plugin_dir, $this->pot_path, $this->domain );
			$error  = isset( $result['error'] ) ? trim( (string) $result['error'] ) : '';
			if ( $error ) {
				error_log( '[i18n-404-tools] POT generation error: ' . $error );
				$output = '';
			} else {
				$output = isset( $result['output'] ) ? $result['output'] : '';
			}
			$output  = esc_html( $output );
			$message = ( $result['success'] && file_exists( $this->pot_path ) )
				? esc_html__( 'POT file generated successfully!', 'i18n-404-tools' )
				: esc_html__( 'An error occurred. Please contact the administrator.', 'i18n-404-tools' );

			return array(
				'html' => '<div class="i18n-modal-content">'
					. $this->generate_modal_header( __( 'Generate .pot', 'i18n-404-tools' ) )
					. '<p><strong>' . $message . '</strong></p>'
					. ( $output ? '<div class="i18n-copy-wrap" style="display:flex;align-items:center;gap:5px;">'
					. '<button type="button" class="button i18n-copy-btn" title="' . esc_attr__( 'Copy output', 'i18n-404-tools' ) . '">'
					. esc_html__( 'Copy output', 'i18n-404-tools' )
					. '</button>'
					. '</div>'
					. '<pre class="i18n-404-tools-output" style="margin-top:10px;max-height:200px;overflow:auto;">' . $output . '</pre>' : '' )
					. $this->generate_cancel_button( __( 'Close', 'i18n-404-tools' ) )
					. '</div>',
			);
		}

		// Fallback for unknown steps.
		return array(
			'html' => '<div class="i18n-modal-content">'
				. '<p>' . esc_html__( 'Unknown step.', 'i18n-404-tools' ) . '</p>'
				. $this->generate_cancel_button( __( 'Close', 'i18n-404-tools' ) )
				. '</div>',
		);
	}
}
