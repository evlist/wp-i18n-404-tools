<?php
/**
 * I18n_404_Tools_Pot_Generator
 * Adds a "Generate .pot" modal with confirmation and AJAX to the Plugins page.
 */

if ( ! class_exists( 'I18n_404_Tools_Pot_Generator' ) ) :

class I18n_404_Tools_Pot_Generator {

    const AJAX_ACTION = 'i18n_404_tools_generate_pot';
    const AJAX_NONCE  = 'i18n_404_tools_generate_pot_nonce';
    const PHAR_REL_PATH = '../bin/wp-cli.phar'; // relative to this file

    public function __construct() {
        add_filter( 'plugin_action_links', [ $this, 'add_generate_pot_link' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'ajax_generate_pot' ] );
    }

    /**
     * Add "Generate .pot" action link to every plugin row.
     */
    public function add_generate_pot_link( $actions, $plugin_file ) {
        if ( strpos( $plugin_file, '/' ) !== false ) {
            $nonce = wp_create_nonce( self::AJAX_NONCE );
            $actions['generate_pot'] = sprintf(
                '<a href="#" class="i18n-404-tools-generate-pot" data-plugin="%s" data-nonce="%s">%s</a>',
                esc_attr( $plugin_file ),
                esc_attr( $nonce ),
                esc_html__( 'Generate .pot', 'i18n-404-tools' )
            );
        }
        return $actions;
    }

    /**
     * Enqueue admin JS and CSS for modal dialog.
     */
    public function enqueue_scripts( $hook ) {
        if ( $hook !== 'plugins.php' ) {
            return;
        }
        // WordPress admin CSS is loaded by default, so no need to enqueue separately
        wp_enqueue_script(
            'i18n-404-tools-pot-generator-vanilla',
            plugins_url( 'admin/js/pot-generator-vanilla.js', dirname(__FILE__, 2) . '/i18n-404-tools.php' ),
            [],
            '1.0',
            true
        );
        wp_localize_script( 'i18n-404-tools-pot-generator-vanilla', 'I18n404PotGen', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'modal_title' => __( 'Generate .pot file', 'i18n-404-tools' ),
            'generating' => __( 'Generating .pot file, please wait...', 'i18n-404-tools' ),
            'overwrite_confirm' => __( 'A .pot file already exists. Overwrite?', 'i18n-404-tools' ),
            'btn_yes' => __( 'Yes, overwrite', 'i18n-404-tools' ),
            'btn_no' => __( 'Cancel', 'i18n-404-tools' ),
        ] );
    }

    /**
     * Handle AJAX request: check existence and/or generate .pot file.
     */
    public function ajax_generate_pot() {
        check_ajax_referer( self::AJAX_NONCE, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'i18n-404-tools' ) ] );
        }

        $plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( $_POST['plugin'] ) : '';
        if ( empty( $plugin ) ) {
            wp_send_json_error( [ 'message' => __( 'Missing plugin parameter.', 'i18n-404-tools' ) ] );
        }

        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin;
        if ( ! file_exists( $plugin_path ) ) {
            wp_send_json_error( [ 'message' => __( 'Plugin not found.', 'i18n-404-tools' ) ] );
        }

        $plugin_dir = dirname( $plugin_path );
        $languages_dir = $plugin_dir . '/languages';
        if ( ! is_dir( $languages_dir ) ) {
            @mkdir( $languages_dir, 0775, true );
        }
        $domain = basename( $plugin, '.php' );
        $pot_path = $languages_dir . '/' . $domain . '.pot';

        // Step 1: If "op" is check, just respond with "exists" or not.
        $op = isset( $_POST['op'] ) ? $_POST['op'] : 'check';
        if ( $op === 'check' ) {
            if ( file_exists( $pot_path ) ) {
                wp_send_json_success( [ 'exists' => true ] );
            } else {
                wp_send_json_success( [ 'exists' => false ] );
            }
        }

        // Step 2: Generate the .pot file (overwrite if needed)
        $overwrite = !empty( $_POST['overwrite'] );
        if ( file_exists( $pot_path ) && ! $overwrite ) {
            wp_send_json_error( [ 'message' => __( 'POT file exists; overwrite not confirmed.', 'i18n-404-tools' ) ] );
        }

        // Build WP-CLI command
        $phar_path = dirname(__FILE__) . '/' . self::PHAR_REL_PATH;
        if ( ! file_exists( $phar_path ) ) {
            wp_send_json_error( [ 'message' => __( 'wp-cli.phar not found.', 'i18n-404-tools' ) ] );
        }

        $php_bin = PHP_BINARY;
        $wp_cli = escapeshellcmd( $php_bin . ' ' . $phar_path );
        $cmd = $wp_cli . ' i18n make-pot '
            . escapeshellarg( $plugin_dir ) . ' '
            . escapeshellarg( $pot_path )
            . ' --exclude=node_modules,vendor,tests,test --domain=' . escapeshellarg( $domain ) . ' --include=php,js';

        @exec( $cmd . ' 2>&1', $output, $exit_code );

        if ( $exit_code === 0 && file_exists( $pot_path ) ) {
            wp_send_json_success( [
                'message' => sprintf( __( 'POT file generated: %s', 'i18n-404-tools' ), esc_html( $pot_path ) ),
            ] );
        } else {
            wp_send_json_error( [
                'message' => __( 'Failed to generate POT file.', 'i18n-404-tools' ) . '<br><pre>' . esc_html( implode( "\n", $output ) ) . '</pre>',
            ] );
        }
    }
}

endif;

// Instantiate the class (do this after plugins_loaded or in your main plugin file)
if ( defined('WP_PLUGIN_DIR') ) {
    new I18n_404_Tools_Pot_Generator();
}
