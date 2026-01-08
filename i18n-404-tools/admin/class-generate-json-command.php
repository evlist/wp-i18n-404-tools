<?php

// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

// File: i18n-404-tools/admin/class-generate-json-command.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/class-i18n-command-base.php';

/**
 * Command for generating JSON translation files from .po files for a plugin.
 */
class I18N_404_Generate_JSON_Command extends I18N_404_Command_Base {

    public function __construct( $plugin ) {
        parent::__construct( $plugin );
        
        // Load modal config and helpers for action attributes
        require_once __DIR__ . '/modal-config.php';
        require_once __DIR__ . '/helpers.php';
    }

    /**
     * Handle specific steps for JSON generation.
     *
     * @param string $step    The requested action: 'check', 'generate', or 'generate_all'
     * @param array  $request The request data, usually $_POST
     * @return array          Response data (with 'html' key)
     */
    public function run_step( $step, $request ) {
        // Step 1: Check .po and .json files status
        if ( $step === 'check' ) {
            return $this->check_files();
        }

        // Step 2: Generate outdated JSON files only
        if ( $step === 'generate' ) {
            return $this->generate_json_files( false );
        }

        // Step 3: Generate all JSON files (overwrite existing)
        if ( $step === 'generate_all' ) {
            return $this->generate_json_files( true );
        }

        // Fallback for unknown steps
        return [
            'error'   => true,
            'message' => __('Unknown step.', 'i18n-404-tools'),
        ];
    }

    /**
     * Check .po and .json files and return appropriate UI.
     *
     * @return array Response with HTML content
     */
    protected function check_files() {
        $po_files = $this->get_po_files();

        // Case 1: No .po files found
        if ( empty( $po_files ) ) {
            return [
                'html' => '<div class="i18n-modal-content">'
                    . '<p>' . esc_html__('No .po translation files found.', 'i18n-404-tools') . '</p>'
                    . '<p>' . esc_html__('Please create translations first (e.g., using Loco Translate or Poedit).', 'i18n-404-tools') . '</p>'
                    . $this->generate_cancel_button( __('Close', 'i18n-404-tools') )
                    . '</div>'
            ];
        }

        // Check if plugin has JavaScript strings that need translation
        if ( ! $this->has_javascript_strings() ) {
            $po_list = '<ul>';
            foreach ( $po_files as $po_file ) {
                $po_list .= '<li>' . esc_html( basename( $po_file ) ) . '</li>';
            }
            $po_list .= '</ul>';

            return [
                'html' => '<div class="i18n-modal-content">'
                    . '<p><strong>' . esc_html__('Translation files found:', 'i18n-404-tools') . '</strong></p>'
                    . $po_list
                    . '<p>' . esc_html__('JSON files not needed.', 'i18n-404-tools') . '</p>'
                    . '<p><em>' . esc_html__('This plugin does not use JavaScript translations (wp.i18n).', 'i18n-404-tools') . '</em></p>'
                    . $this->generate_cancel_button( __('Close', 'i18n-404-tools') )
                    . '</div>'
            ];
        }

        // Check for JSON files
        $json_status = $this->get_json_status( $po_files );
        $has_json = ! empty( $json_status['existing'] );
        $has_outdated = ! empty( $json_status['outdated'] );

        $po_list = '<ul>';
        foreach ( $po_files as $po_file ) {
            $po_list .= '<li>' . esc_html( basename( $po_file ) ) . '</li>';
        }
        $po_list .= '</ul>';

        // Case 2: No JSON files exist
        if ( ! $has_json ) {
            return [
                'html' => '<div class="i18n-modal-content">'
                    . '<p><strong>' . esc_html__('Translation files found:', 'i18n-404-tools') . '</strong></p>'
                    . $po_list
                    . '<p>' . esc_html__('No JSON files found. Generate them now?', 'i18n-404-tools') . '</p>'
                    . $this->generate_action_button(
                        __('Generate JSON files', 'i18n-404-tools'),
                        'generate_json',
                        'generate_all',
                        'button-primary'
                    ) . ' '
                    . $this->generate_cancel_button( __('Cancel', 'i18n-404-tools') )
                    . '</div>'
            ];
        }

        // Case 3: JSON files exist
        $html = '<div class="i18n-modal-content">'
            . '<p><strong>' . esc_html__('Translation files found:', 'i18n-404-tools') . '</strong></p>'
            . $po_list;

        if ( $has_outdated ) {
            $outdated_count = count( $json_status['outdated'] );
            $html .= '<p>' 
                . sprintf( 
                    _n( 
                        '%d JSON file is outdated or missing.', 
                        '%d JSON files are outdated or missing.', 
                        $outdated_count, 
                        'i18n-404-tools' 
                    ), 
                    $outdated_count 
                ) 
                . '</p>';
            
            $html .= '<p>' . esc_html__('What would you like to do?', 'i18n-404-tools') . '</p>'
                . $this->generate_action_button(
                    __('Generate outdated JSON files', 'i18n-404-tools'),
                    'generate_json',
                    'generate',
                    'button-primary'
                ) . ' '
                . $this->generate_action_button(
                    __('Generate all JSON files', 'i18n-404-tools'),
                    'generate_json',
                    'generate_all',
                    ''
                ) . ' '
                . $this->generate_cancel_button( __('Cancel', 'i18n-404-tools') );
        } else {
            $html .= '<p>' . esc_html__('All JSON files are up to date.', 'i18n-404-tools') . '</p>'
                . '<p>' . esc_html__('Would you like to regenerate them anyway?', 'i18n-404-tools') . '</p>'
                . $this->generate_action_button(
                    __('Regenerate all JSON files', 'i18n-404-tools'),
                    'generate_json',
                    'generate_all',
                    'button-primary'
                ) . ' '
                . $this->generate_cancel_button( __('Cancel', 'i18n-404-tools') );
        }

        $html .= '</div>';

        return [ 'html' => $html ];
    }

    /**
     * Generate JSON files.
     *
     * @param bool $generate_all Whether to generate all files or only outdated ones
     * @return array Response with HTML content
     */
    protected function generate_json_files( $generate_all ) {
        $po_files = $this->get_po_files();
        
        if ( empty( $po_files ) ) {
            return [
                'html' => '<div class="i18n-modal-content">'
                    . '<p>' . esc_html__('No .po files found.', 'i18n-404-tools') . '</p>'
                    . $this->generate_cancel_button( __('Close', 'i18n-404-tools') )
                    . '</div>'
            ];
        }

        // Filter files if not generating all
        if ( ! $generate_all ) {
            $json_status = $this->get_json_status( $po_files );
            $po_files = $json_status['outdated'];
        }

        $results = [];
        $overall_success = true;

        foreach ( $po_files as $po_file ) {
            $result = $this->run_wp_cli_command(
                'i18n make-json',
                [
                    0          => $this->languages_dir,
                    'no-purge' => null,
                ]
            );

            $results[] = [
                'file'       => basename( $po_file ),
                'success'    => $result['exit_code'] === 0,
                'output'     => trim( $result['stdout'] . "\n" . $result['stderr'] ),
            ];

            if ( $result['exit_code'] !== 0 ) {
                $overall_success = false;
            }
        }

        // Build output HTML
        $html = '<div class="i18n-modal-content">';
        
        if ( $overall_success ) {
            $html .= '<p><strong>' . esc_html__('JSON files generated successfully!', 'i18n-404-tools') . '</strong></p>';
        } else {
            $html .= '<p><strong>' . esc_html__('Some JSON files failed to generate.', 'i18n-404-tools') . '</strong></p>';
        }

        // Show results for each file
        $html .= '<div style="margin-top:12px;">';
        foreach ( $results as $result ) {
            $status_icon = $result['success'] ? '✓' : '✗';
            $status_color = $result['success'] ? 'green' : 'red';
            $html .= '<p style="margin: 5px 0;">'
                . '<span style="color:' . esc_attr( $status_color ) . ';font-weight:bold;">' . esc_html( $status_icon ) . '</span> '
                . esc_html( $result['file'] )
                . '</p>';
        }
        $html .= '</div>';

        // Show command output
        $combined_output = '';
        foreach ( $results as $result ) {
            if ( ! empty( $result['output'] ) ) {
                $combined_output .= $result['output'] . "\n";
            }
        }

        if ( ! empty( $combined_output ) ) {
            $html .= '<div class="i18n-copy-wrap" style="display:flex;align-items:center;gap:5px;margin-top:12px;">'
                . '<button type="button" class="button i18n-copy-btn" title="' . esc_attr__('Copy output', 'i18n-404-tools') . '">'
                . '<span class="dashicons dashicons-clipboard"></span>'
                . '</button>'
                . '<pre class="i18n-modal-output" style="flex:1;overflow:auto;max-height:300px;background:#f6f7f7;padding:8px;border-radius:3px;">'
                . esc_html( trim( $combined_output ) )
                . '</pre>'
                . '</div>';
        }

        $html .= '<div style="margin-top:12px;">'
            . $this->generate_cancel_button( __('Close', 'i18n-404-tools') )
            . '</div>'
            . '</div>';

        return [ 'html' => $html ];
    }

    /**
     * Get all .po files in the languages directory.
     *
     * @return array List of .po file paths
     */
    protected function get_po_files() {
        if ( ! is_dir( $this->languages_dir ) ) {
            return [];
        }

        $po_files = glob( $this->languages_dir . '/' . $this->domain . '-*.po' );
        return is_array( $po_files ) ? $po_files : [];
    }

    /**
     * Get JSON file status for given .po files.
     *
     * @param array $po_files List of .po file paths
     * @return array Status array with 'existing' and 'outdated' keys
     */
    protected function get_json_status( $po_files ) {
        $existing = [];
        $outdated = [];

        foreach ( $po_files as $po_file ) {
            $po_basename = basename( $po_file, '.po' );
            
            // Look for JSON files matching this locale
            // Format: domain-locale-hash.json
            $json_pattern = $this->languages_dir . '/' . $po_basename . '-*.json';
            $json_files = glob( $json_pattern );

            if ( empty( $json_files ) ) {
                // No JSON files for this .po file
                $outdated[] = $po_file;
            } else {
                $existing = array_merge( $existing, $json_files );
                
                // Check if any JSON file is older than the .po file
                $po_mtime = filemtime( $po_file );
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

        return [
            'existing' => $existing,
            'outdated' => $outdated,
        ];
    }

    /**
     * Check if the plugin has JavaScript files that use wp.i18n for translations.
     *
     * @return bool True if JavaScript translation strings are found
     */
    protected function has_javascript_strings() {
        // Check if there are any .js files in the plugin directory
        $js_files = $this->find_js_files( $this->plugin_dir );
        
        if ( empty( $js_files ) ) {
            return false;
        }

        // Look for wp.i18n usage patterns in JavaScript files
        foreach ( $js_files as $js_file ) {
            $content = @file_get_contents( $js_file );
            if ( $content === false ) {
                continue;
            }

            // Check for wp.i18n.__ or wp.i18n._x or similar patterns
            if ( preg_match( '/wp\.i18n\.[_a-z]+\s*\(/', $content ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find all JavaScript files in a directory.
     *
     * @param string $dir Directory to search
     * @return array List of JavaScript file paths
     */
    protected function find_js_files( $dir ) {
        $js_files = [];
        
        if ( ! is_dir( $dir ) ) {
            return $js_files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS )
        );

        foreach ( $iterator as $file ) {
            if ( $file->isFile() && $file->getExtension() === 'js' ) {
                $js_files[] = $file->getPathname();
            }
        }

        return $js_files;
    }
}
