<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://e.vli.st
 * @since             1.0.0
 * @package           I18n_404_Tools
 *
 * @wordpress-plugin
 * Plugin Name:       Missing i18n tools
 * Plugin URI:        https://https://github.com/evlist/wp-i18n-404-tools
 * Description:       A WordPress plugin with missing i18N (internationalization) tools.
 * Version:           1.0.0
 * Author:            Eric van der Vlist
 * Author URI:        https://e.vli.st/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       i18n-404-tools
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'I18N_404_TOOLS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-i18n-404-tools-activator.php
 */
require_once plugin_dir_path(__FILE__) . 'admin/class-wpcli-updater.php';
function activate_i18n_404_tools() {
    if ( isset( $GLOBALS['i18n_404_tools_wpcli_updater'] ) ) {
        error_log('download_phar_with_notice: '.$GLOBALS['i18n_404_tools_wpcli_updater']->download_phar_with_notice());
    }
}
register_activation_hook( __FILE__, 'activate_i18n_404_tools' );

// Load translations if available
add_action('plugins_loaded', function() {
        load_plugin_textdomain('i18n-404-tools', false, dirname(plugin_basename(__FILE__)) . '/languages');
        });

// Only load base and router for our AJAX requests; router will load command classes dynamically
add_action('init', function() {
        if (
                defined('DOING_AJAX') && DOING_AJAX &&
                isset($_REQUEST['action']) && $_REQUEST['action'] === 'i18n_404_tools_command'
           ) {
        require_once plugin_dir_path(__FILE__) . 'admin/class-i18n-command-base.php';
        require_once plugin_dir_path(__FILE__) . 'admin/class-i18n-ajax-router.php';
        new I18N_404_Ajax_Router();
        }
        });

// Add the "Generate .pot" link to each plugin row on the Plugins page
add_filter('plugin_action_links', function($actions, $plugin_file) {
        if ( current_user_can('manage_options') ) {
        $actions['i18n_pot'] = '<a href="#" class="i18n-404-tools-action" data-plugin="' . esc_attr($plugin_file) . '" data-command="generate_pot" data-step="check">' . esc_html__('Generate .pot', 'i18n-404-tools') . '</a>';
        }
        return $actions;
        }, 10, 2);

// Enqueue JS and Dashicons only for the Plugins page
// /css/i18n-404-tools-admin.css
add_action('admin_enqueue_scripts', function($hook) {
        if ( $hook !== 'plugins.php' ) {
        return;
        }
        wp_enqueue_script(
                'i18n-404-tools-modal',
                plugins_url('admin/js/i18n-404-tools-modal.js', __FILE__),
                [],
                '1.0',
                true
                );
        wp_enqueue_style('dashicons');
        wp_localize_script(
                'i18n-404-tools-modal',
                'I18n404PotGen',
                [
                'ajax_url' => admin_url('admin-ajax.php'),
                ]
                );
        wp_enqueue_style(
                'i18n-404-tools-admin',
                plugins_url('admin/css/i18n-404-tools-admin.css', __FILE__),
                [],
                '1.0'
                );
});
