<?php
// File: i18n-404-tools/admin/class-i18n-command-base.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Make sure the WPCLI updater class is loaded
require_once __DIR__ . '/class-wpcli-updater.php';

/**
 * Abstract base class for i18n-404-tools commands.
 */
abstract class I18N_404_Command_Base {

    /** @var string Plugin file slug (e.g., hello-dolly/hello.php) */
    protected $plugin;

    /** @var string Full path to the plugin main file */
    protected $plugin_path;

    /** @var string Directory containing the plugin */
    protected $plugin_dir;

    /** @var string Directory for languages/ */
    protected $languages_dir;

    /** @var string Text domain (guessed from plugin main file) */
    protected $domain;

    /** @var string Full path to the .pot file */
    protected $pot_path;

    /**
     * Set up context for the command.
     *
     * @param string $plugin The plugin file slug (e.g. hello-dolly/hello.php)
     * @throws Exception If plugin is missing or paths are invalid
     */
    public function __construct( $plugin ) {
        $this->plugin = sanitize_text_field( $plugin );
        $this->plugin_path = WP_PLUGIN_DIR . '/' . $this->plugin;

        if ( ! file_exists( $this->plugin_path ) ) {
            throw new Exception( __( 'Plugin not found.', 'i18n-404-tools' ) );
        }

        $this->plugin_dir = dirname( $this->plugin_path );
        $this->languages_dir = $this->plugin_dir . '/languages';

        if ( ! is_dir( $this->languages_dir ) ) {
            // Attempt to create the languages dir, but ignore errors
            @mkdir( $this->languages_dir, 0775, true );
        }

        $this->domain = basename( $this->plugin, '.php' );
        $this->pot_path = $this->languages_dir . '/' . $this->domain . '.pot';
    }

    /**
     * Entrypoint for command execution.
     * Must be implemented by child classes.
     *
     * @param string $step    The sub-action/step to perform (e.g. 'check', 'generate')
     * @param array  $request Full request (usually $_POST)
     * @return array          Result data for Ajax response
     */
    abstract public function run_step( $step, $request );

    /**
     * Run a WP-CLI command with flexible arguments.
     * - Numeric keys are positional arguments.
     * - String keys with null values become flags (--foo).
     * - String keys with values become options (--foo="bar").
     *
     * @param string $subcommand E.g., 'i18n make-pot'
     * @param array  $args       Command arguments and flags.
     * @param string $cwd        Optional working directory.
     * @return array             ['stdout' => ..., 'stderr' => ..., 'exit_code' => ...]
     */
    protected function run_wp_cli_command( $subcommand, array $args = [], $cwd = null ) {
        // Use the predefined PHP binary if available, fallback to PHP_BINARY
        $php_path = defined( 'WP_CLI_PHP_BINARY' ) ? WP_CLI_PHP_BINARY : PHP_BINARY;

        // Get the WP-CLI phar path from updater class
        $wp_cli_phar = I18n_404_Tools_WPCLI_Updater::get_phar_path();

        $cmd_parts = [
            escapeshellcmd( $php_path ),
            escapeshellarg( $wp_cli_phar ),
        ];

        // Add the subcommand (e.g. 'i18n make-pot' -> ['i18n', 'make-pot'])
        foreach ( explode( ' ', $subcommand ) as $part ) {
            $cmd_parts[] = escapeshellcmd( $part );
        }

        // Add arguments and flags
        foreach ( $args as $key => $value ) {
            if ( is_int( $key ) ) {
                // Positional argument
                $cmd_parts[] = escapeshellarg( $value );
            } else {
                // Option or flag
                if ( is_null( $value ) ) {
                    $cmd_parts[] = '--' . escapeshellcmd( $key );
                } else {
                    $cmd_parts[] = '--' . escapeshellcmd( $key ) . '=' . escapeshellarg( $value );
                }
            }
        }

        $cmd = implode( ' ', $cmd_parts );

        $descriptorspec = [
            1 => [ 'pipe', 'w' ], // stdout
            2 => [ 'pipe', 'w' ], // stderr
        ];

        $process = proc_open( $cmd, $descriptorspec, $pipes, $cwd ?: null );

        if ( is_resource( $process ) ) {
            $stdout    = stream_get_contents( $pipes[1] );
            fclose( $pipes[1] );
            $stderr    = stream_get_contents( $pipes[2] );
            fclose( $pipes[2] );
            $exit_code = proc_close( $process );

            return [
                'stdout'    => $stdout,
                'stderr'    => $stderr,
                'exit_code' => $exit_code,
            ];
        } else {
            return [
                'stdout'    => '',
                'stderr'    => 'Could not open process.',
                'exit_code' => 1,
            ];
        }
    }
}
