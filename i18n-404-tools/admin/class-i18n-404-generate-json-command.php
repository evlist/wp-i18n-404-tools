<?php
/**
 * Generate JSON command class.
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
 * Command for generating JSON translation files from .po files for a plugin.
 */
class I18N_404_Generate_JSON_Command extends I18N_404_Command_Base {
	

	/**
	 * Set up the JSON command handler.
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
	 * Handle specific steps for JSON generation.
	 *
	 * @param string $step    The requested action: 'check', 'generate', or 'generate_all'.
	 * @param array  $request The request data, usually $_POST.
	 * @return array          Response data (with 'html' key).
	 */
	public function run_step( $step, $request ) {
		// Step 1: Check .po and .json files status.
		if ( 'check' === $step ) {
			return $this->check_files();
		}

		// Step 2: Generate outdated JSON files only.
		if ( 'generate' === $step ) {
			return $this->generate_json_files( false );
		}

		// Step 3: Generate all JSON files (overwrite_non_outdated existing).
		if ( 'generate_all' === $step ) {
			return $this->generate_json_files( true );
		}

		// Fallback for unknown steps.
		return array(
			'error'   => true,
			'message' => __( 'Unknown step.', 'i18n-404-tools' ),
		);
	}

	/**
	 * Check .po and .json files and return appropriate UI.
	 *
	 * @return array Response with HTML content
	 */
	protected function check_files() {
		$po_files = $this->get_po_files();

		// Case 1: No .po files found.
		if ( empty( $po_files ) ) {
			return array(
				'html' => '<div class="i18n-modal-content">'
					. $this->generate_modal_header( __( 'Generate JSON', 'i18n-404-tools' ) )
					. '<p>' . esc_html__( 'No .po translation files found.', 'i18n-404-tools' ) . '</p>'
					. '<p>' . esc_html__( 'Please create translations first (e.g., using Loco Translate or Poedit).', 'i18n-404-tools' ) . '</p>'
					. $this->generate_cancel_button( __( 'Close', 'i18n-404-tools' ) )
					. '</div>',
			);
		}

		// Check if plugin has JavaScript strings that need translation.
		if ( ! $this->has_javascript_strings() ) {
			$po_list = '<ul>';
			foreach ( $po_files as $po_file ) {
				$po_list .= '<li>' . esc_html( basename( $po_file ) ) . '</li>';
			}
			$po_list .= '</ul>';

			return array(
				'html' => '<div class="i18n-modal-content">'
					. $this->generate_modal_header( __( 'Generate JSON', 'i18n-404-tools' ) )
					. '<p><strong>' . esc_html__( 'Translation files found:', 'i18n-404-tools' ) . '</strong></p>'
					. $po_list
					. '<p>' . esc_html__( 'JSON files not needed.', 'i18n-404-tools' ) . '</p>'
					. '<p><em>' . esc_html__( 'This plugin does not use JavaScript translations (wp.i18n).', 'i18n-404-tools' ) . '</em></p>'
					. $this->generate_cancel_button( __( 'Close', 'i18n-404-tools' ) )
					. '</div>',
			);
		}

		// Check for JSON files.
		$json_status  = $this->get_json_status( $po_files );
		$has_json     = ! empty( $json_status['existing'] );
		$has_outdated = ! empty( $json_status['outdated'] );

		$po_list = '<ul>';
		foreach ( $po_files as $po_file ) {
			$po_list .= '<li>' . esc_html( basename( $po_file ) ) . '</li>';
		}
		$po_list .= '</ul>';

		// Case 2: No JSON files exist.
		if ( ! $has_json ) {
			return array(
				'html' => '<div class="i18n-modal-content">'
					. $this->generate_modal_header( __( 'Generate JSON', 'i18n-404-tools' ) )
					. '<p><strong>' . esc_html__( 'Translation files found:', 'i18n-404-tools' ) . '</strong></p>'
					. $po_list
					. '<p>' . esc_html__( 'No JSON files found. Generate them now?', 'i18n-404-tools' ) . '</p>'
					. $this->generate_action_button(
						__( 'Generate JSON files', 'i18n-404-tools' ),
						'generate_json',
						'generate_all',
						'button-primary'
					) . ' '
					. $this->generate_cancel_button( __( 'Cancel', 'i18n-404-tools' ) )
					. '</div>',
			);
		}

		   // Case 3: JSON files exist.
		   $html = '<div class="i18n-modal-content">'
			   . $this->generate_modal_header( __( 'Generate JSON', 'i18n-404-tools' ) )
			   . '<p><strong>' . esc_html__( 'Translation files found:', 'i18n-404-tools' ) . '</strong></p>'
			   . $po_list;

			$outdated_count = count( $json_status['outdated'] );
			$html          .= '<p>'
				. sprintf(
					// Translators: %d is the number of JSON files that are outdated or missing.
					_n(
						'%d JSON file is outdated or missing.',
						'%d JSON files are outdated or missing.',
						$outdated_count,
						'i18n-404-tools'
					),
					$outdated_count
				)
				. '</p>';

			$html .= $this->generate_action_button(
					__( 'Generate outdated JSON files', 'i18n-404-tools' ),
					'generate_json',
					'generate',
					'button-primary' . ( $outdated_count === 0 ? ' disabled' : '' ) // Disable if none outdated
				) . ' '
				. $this->generate_action_button(
				   __( 'Generate all JSON files', 'i18n-404-tools' ),
				   'generate_json',
				   'generate_all',
				   'button-primary'
			   ) . ' '
			   . $this->generate_cancel_button( __( 'Cancel', 'i18n-404-tools' ) );

		   return array( 'html' => $html );
	}

	// ... autres méthodes ...

	/**
	 * Get all .po files in the languages directory.
	 *
	 * @return array List of .po file paths
	 */
	protected function get_po_files() {
		if ( ! is_dir( $this->languages_dir ) ) {
			return array();
		}

		$po_files = glob( $this->languages_dir . '/' . $this->domain . '-*.po' );
		return is_array( $po_files ) ? $po_files : array();
	}

	/**
	 * Get JSON file status for given .po files.
	 *
	 * @param array $po_files List of .po file paths.
	 * @return array Status array with 'existing' and 'outdated' keys.
	 */
	protected function get_json_status( $po_files ) {
		$existing = array();
		$outdated = array();

		foreach ( $po_files as $po_file ) {
			$po_basename = basename( $po_file, '.po' );

			// Look for JSON files matching this locale.
			// Format: domain-locale-hash.json.
			$json_pattern = $this->languages_dir . '/' . $po_basename . '-*.json';
			$json_files   = glob( $json_pattern );

			if ( empty( $json_files ) ) {
				// No JSON files for this .po file.
				$outdated[] = $po_file;
			} else {
				$existing = array_merge( $existing, $json_files );

				// Check if any JSON file is older than the .po file.
				$po_mtime       = @filemtime( $po_file );
				$all_up_to_date = true;

				foreach ( $json_files as $json_file ) {
					if ( filemtime( $json_file ) < $po_mtime ) {
						$all_up_to_date = false;
						break;
					}
				}

				if ( ! $all_up_to_date ) {
					$outdated[] = $po_file;
				}
			}
		}

		return array(
			'existing' => $existing,
			'outdated' => $outdated,
		);
	}

	/**
	 * Check if the .pot file contains references to JavaScript files.
	 *
	 * @return bool True if JS references are found, false otherwise.
	 */
	protected function has_javascript_strings() {

	    $handle = fopen( $this->pot_path, 'r' );
		if ( ! $handle ) {
			return false;
		}

		while ( ( $line = fgets( $handle ) ) !== false ) {
			// Match lines starting with #: and containing a filename ending with .js
			if ( preg_match( '/^#:.*\.js(:\d+)?\s?$/', $line ) ) {
				fclose( $handle );
				return true;
			}
		}

		fclose( $handle );

		return false;
	}

	/**
	 * Generate JSON files from .po files.
	 *
	 * @param bool $overwrite_non_outdated If true, (re)generate all JSON files. If false, only generate for outdated/missing.
	 * @return array Result array with HTML content and success flag.
	 */
	protected function generate_json_files( $overwrite_non_outdated = false ) {
		$po_files = $this->get_po_files();
		$results  = array();
		$success  = true;

		if ( empty( $po_files ) ) {
			return array(
				'html' => '<div class="i18n-modal-content">'
					. $this->generate_modal_header( __( 'Generate JSON', 'i18n-404-tools' ) )
					. '<p>' . esc_html__( 'No .po files found.', 'i18n-404-tools' ) . '</p>'
					. $this->generate_cancel_button( __( 'Close', 'i18n-404-tools' ) )
					. '</div>',
			);
		}

		// Get status to determine which .po files are outdated/missing JSON.
		$json_status = $this->get_json_status( $po_files );
		$target_po_files = $overwrite_non_outdated
			? $po_files
			: $json_status['outdated'];

		if ( empty( $target_po_files ) ) {
			return array(
				'html' => '<div class="i18n-modal-content">'
					. $this->generate_modal_header( __( 'Generate JSON', 'i18n-404-tools' ) )
					. '<p>' . esc_html__( 'All JSON files are up to date.', 'i18n-404-tools' ) . '</p>'
					. $this->generate_cancel_button( __( 'Close', 'i18n-404-tools' ) )
					. '</div>',
				'success' => true,
			);
		}

		$assoc_args = array(
			'output' => $this->languages_dir,
			'force'  => true, // Always force overwrite for selected files.
			'quiet'  => true,
		);

		foreach ( $target_po_files as $po_file ) {
			$ret = $this->extractor->generate_json( $po_file, $assoc_args );
			if ( is_array( $ret ) && ! empty( $ret['success'] ) ) {
				$locale = basename( $po_file, '.po' );
				$json_files = glob( $this->languages_dir . '/' . $locale . '-*.json' );
				foreach ( $json_files as $json_file ) {
					$results[] = array(
						'file'    => basename( $json_file ),
						'status'  => 'generated',
						'message' => __( 'Generated', 'i18n-404-tools' ),
					);
				}
			} else {
				$results[] = array(
					'file'    => basename( $po_file ),
					'status'  => 'error',
					'message' => is_array($ret) && !empty($ret['error']) ? $ret['error'] : __( 'Unknown error', 'i18n-404-tools' ),
				);
				$success = false;
			}
		}

		$html = '<div class="i18n-modal-content">'
			. $this->generate_modal_header( __( 'Generate JSON', 'i18n-404-tools' ) )
			. '<ul>';
		foreach ( $results as $result ) {
			$html .= '<li><strong>' . esc_html( $result['file'] ) . '</strong>: '
				. esc_html( $result['message'] ) . '</li>';
		}
		$html .= '</ul>'
			. $this->generate_cancel_button( __( 'Close', 'i18n-404-tools' ) )
			. '</div>';

		return array(
			'html'    => $html,
			'success' => $success,
		);
	}
}
