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
};



register_activation_hook( __FILE__, 'activate_i18n_404_tools' );

