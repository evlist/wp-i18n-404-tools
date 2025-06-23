<?php
// File: i18n-404-tools/admin/class-generate-pot-command.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/class-i18n-command-base.php';

/**
 * Command for generating a .pot file for a plugin (with generic modal HTML output).
 */
class I18N_404_Generate_Pot_Command extends I18N_404_Command_Base {

    /**
     * Handle specific steps for .pot generation.
     *
     * @param string $step    The requested action: 'check' or 'generate'
     * @param array  $request The request data, usually $_POST
     * @return array          Response data (with 'html' key)
     */
    public function run_step( $step, $request ) {
        // Step 1: Check if a .pot file exists and show confirmation
        if ( $step === 'check' ) {
            if ( file_exists( $this->pot_path ) ) {
                return [
                    'html' => '<div class="i18n-modal-content">'
                        . '<p>' . esc_html__('A .pot file already exists. Overwrite?', 'i18n-404-tools') . '</p>'
                        . '<button type="button" class="button button-primary i18n-confirm" data-step="generate" data-overwrite="1">'
                        . esc_html__('Yes, overwrite', 'i18n-404-tools')
                        . '</button> '
                        . '<button type="button" class="button i18n-cancel">'
                        . esc_html__('Cancel', 'i18n-404-tools')
                        . '</button>'
                        . '</div>'
                ];
            } else {
                return [
                    'html' => '<div class="i18n-modal-content">'
                        . '<p>' . esc_html__('No .pot file exists. Generate now?', 'i18n-404-tools') . '</p>'
                        . '<button type="button" class="button button-primary i18n-confirm" data-step="generate">'
                        . esc_html__('Generate', 'i18n-404-tools')
                        . '</button> '
                        . '<button type="button" class="button i18n-cancel">'
                        . esc_html__('Cancel', 'i18n-404-tools')
                        . '</button>'
                        . '</div>'
                ];
            }
        }

        // Step 2: Generate the .pot file and return output/result
        if ( $step === 'generate' ) {
            $overwrite = ! empty( $request['overwrite'] );
            if ( file_exists( $this->pot_path ) && ! $overwrite ) {
                return [
                    'html' => '<div class="i18n-modal-content">'
                        . '<p>' . esc_html__('POT file exists; overwrite not confirmed.', 'i18n-404-tools') . '</p>'
                        . '<button type="button" class="button i18n-close">'
                        . esc_html__('Close', 'i18n-404-tools')
                        . '</button>'
                        . '</div>'
                ];
            }

            $result = $this->run_wp_cli_command(
                'i18n make-pot',
                [
                    0        => $this->plugin_dir,
                    1        => $this->pot_path,
                    'domain' => $this->domain,
                ]
            );

            $output = esc_html(trim($result['stdout'] . "\n" . $result['stderr']));
            if ( $result['exit_code'] === 0 && file_exists( $this->pot_path ) ) {
                $message = esc_html__('POT file generated successfully!', 'i18n-404-tools');
            } else {
                $message = esc_html__('Failed to generate POT file.', 'i18n-404-tools');
            }

            return [
                'html' => '<div class="i18n-modal-content">'
                    . '<p><strong>' . $message . '</strong></p>'
                    . '<div class="i18n-copy-wrap" style="display:flex;align-items:center;gap:5px;">'
                    . '<button type="button" class="button i18n-copy-btn" title="' . esc_attr__('Copy output', 'i18n-404-tools') . '">'
                    . '<span class="dashicons dashicons-clipboard"></span>'
                    . '</button>'
                    . '<pre class="i18n-modal-output" style="flex:1;overflow:auto;max-height:300px;background:#f6f7f7;padding:8px;border-radius:3px;">'
                    . $output
                    . '</pre>'
                    . '</div>'
                    . '<button type="button" class="button i18n-close" style="margin-top:12px;">'
                    . esc_html__('Close', 'i18n-404-tools')
                    . '</button>'
                    . '</div>'
            ];
        }

        // Fallback for unknown steps
        return [
            'html' => '<div class="i18n-modal-content">'
                . '<p>' . esc_html__('Unknown step.', 'i18n-404-tools') . '</p>'
                . '<button type="button" class="button i18n-close">'
                . esc_html__('Close', 'i18n-404-tools')
                . '</button>'
                . '</div>'
        ];
    }
}
