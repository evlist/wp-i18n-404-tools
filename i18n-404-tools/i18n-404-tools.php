<?php
/**
 * Main plugin file.
 *
 * @package I18n_404_Tools
 */

/*
 * SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

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
 * Plugin Name:       Missing i18n Tools
 * Plugin URI:        https://github.com/evlist/wp-i18n-404-tools
 * Description:       A WordPress plugin with missing i18N (internationalization) tools.
 * Version:           1.1.0
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
define( 'I18N_404_TOOLS_VERSION', '1.1.0' );

// Only load base and router for our AJAX requests; router will load command classes dynamically.
add_action(
	'init',
	function () {
		if (
			defined( 'DOING_AJAX' ) && DOING_AJAX &&
			isset( $_REQUEST['action'] ) && 'i18n_404_tools_command' === $_REQUEST['action'] &&
			isset( $_REQUEST['_ajax_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_ajax_nonce'] ) ), 'i18n_404_tools_action' )
			) {
			require_once plugin_dir_path( __FILE__ ) . 'admin/class-i18n-404-command-base.php';
					require_once plugin_dir_path( __FILE__ ) . 'admin/class-i18n-404-ajax-router.php';
			new I18N_404_Ajax_Router();
		}
	}
);

// Add the "Generate .pot" and "Generate JSON" links to each plugin row on the Plugins page.
add_filter(
	'plugin_action_links',
	function ( $actions, $plugin_file ) {
		if ( current_user_can( 'manage_options' ) ) {
			// Load modal config and helpers for the action attributes.
			require_once plugin_dir_path( __FILE__ ) . 'admin/modal-config.php';
			require_once plugin_dir_path( __FILE__ ) . 'admin/helpers.php';

			$logo_img = '<img src="' . esc_url( plugins_url( 'admin/images/logo.svg', __FILE__ ) ) . '" alt="" style="height:16px;width:16px;margin-right:5px;vertical-align:-2px;" />';

			// Extract plugin slug from plugin_file (e.g. 'i18n-404-tools/i18n-404-tools.php' => 'i18n-404-tools').
			$plugin_slug = preg_replace( '/\/.*$/', '', $plugin_file );
			$attrs_pot           = i18n_404_tools_action_attrs( 'generate_pot', $plugin_slug );
			$actions['i18n_pot'] = '<a href="#" ' . $attrs_pot . '>' . $logo_img . esc_html__( 'Generate .pot', 'i18n-404-tools' ) . '</a>';

			$attrs_json           = i18n_404_tools_action_attrs( 'generate_json', $plugin_slug );
			$actions['i18n_json'] = '<a href="#" ' . $attrs_json . '>' . $logo_img . esc_html__( 'Generate JSON', 'i18n-404-tools' ) . '</a>';
		}
		return $actions;
	},
	10,
	2
);

// Enqueue JS and Dashicons only for the Plugins page.
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'plugins.php' !== $hook ) {
			return;
		}
		wp_enqueue_script(
			'i18n-404-tools-modal',
			plugins_url( 'admin/js/i18n-404-tools-modal.js', __FILE__ ),
			array( 'wp-i18n' ),
			'1.0',
			true
		);
		wp_set_script_translations( 'i18n-404-tools-modal', 'i18n-404-tools', plugin_dir_path( __FILE__ ) . 'languages' );
		wp_enqueue_style( 'dashicons' );
		require plugin_dir_path( __FILE__ ) . 'admin/modal-config.php';
		global $i18n_404_tools_modal_config;
		wp_localize_script(
			'i18n-404-tools-modal',
			'I18n404ToolsConfig',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'i18n_404_tools_action' ),
				'ui'       => $i18n_404_tools_modal_config,
			)
		);
		wp_enqueue_style(
			'i18n-404-tools-admin',
			plugins_url( 'admin/css/i18n-404-tools-admin.css', __FILE__ ),
			array(),
			'1.0'
		);
	}
);
