<?php

// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

/**
 * AJAX Router for WP i18n 404 Tools
 *
 * @package I18n_404_Tools
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class I18N_404_Ajax_Router {

    /**
     * Map commands to [class_name, file_name]
     */
    protected $commands = [
        // 'command'         => [ 'ClassName',                 'file-name.php' ]
        'generate_pot'      => [ 'I18N_404_Generate_Pot_Command', 'class-generate-pot-command.php' ],
        'generate_json'     => [ 'I18N_404_Generate_JSON_Command', 'class-generate-json-command.php' ],
        // Add more mappings as needed:
        // 'other_command'   => [ 'I18N_404_Other_Command',    'class-other-command.php' ],
    ];

    public function __construct() {
        add_action('wp_ajax_i18n_404_tools_command', [ $this, 'handle_ajax' ]);
    }

    public function handle_ajax() {
        if ( ! current_user_can('manage_options') ) {
            wp_send_json_error(['message' => __('Unauthorized request.', 'i18n-404-tools')], 403);
        }

        check_ajax_referer('i18n_404_tools_action');

        $plugin_slug = isset($_POST['plugin']) ? sanitize_text_field($_POST['plugin']) : '';
        $command     = isset($_POST['command']) ? sanitize_key($_POST['command']) : '';
        $step        = isset($_POST['step']) ? sanitize_key($_POST['step']) : '';
        $request     = $_POST;

        if (empty($plugin_slug) || empty($command) || empty($step)) {
            wp_send_json_error(['message' => __('Missing required parameters.', 'i18n-404-tools')]);
        }

        if ( ! isset($this->commands[ $command ]) ) {
            wp_send_json_error(['message' => __('Unknown command.', 'i18n-404-tools')]);
        }
        list($class_name, $file_name) = $this->commands[ $command ];

        if ( ! class_exists( $class_name ) ) {
            $file_path = dirname(__FILE__) . '/' . $file_name;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            } else {
                wp_send_json_error(['message' => __('Command file not found: ', 'i18n-404-tools') . $file_name]);
            }
        }

        if ( ! class_exists($class_name) ) {
            wp_send_json_error(['message' => __('Command class not found after loading file.', 'i18n-404-tools')]);
        }

        try {
            $handler = new $class_name($plugin_slug);
            $result = $handler->run_step($step, $request);

            if (isset($result['error'])) {
                wp_send_json_error($result);
            } else {
                wp_send_json_success($result);
            }
        } catch ( Exception $e ) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
        wp_die();
    }
}
