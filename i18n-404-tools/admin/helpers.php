<?php
/**
 * Helper functions for modal dialogs.
 *
 * @package I18n_404_Tools
 */

// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

/**
 * Output attributes for a modal action element.
 *
 * @param string $command        Command name.
 * @param string $plugin         Plugin slug.
 * @param string $step           Step name.
 * @param string $extra_classes  Optional extra CSS classes.
 * @return string Attribute string.
 */
function i18n404tools_action_attrs( $command, $plugin, $step = 'check', $extra_classes = '' ) {
	global $i18n404tools_modal_config; // Assume this is set elsewhere.
	$attrs = array();

	// Class attribute.
	$classes = array( $i18n404tools_modal_config['action_class'] );
	if ( $extra_classes ) {
		$classes[] = $extra_classes;
	}
	$attrs[] = 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';

	// Data attributes.
	$attrs[] = $i18n404tools_modal_config['data_command'] . '="' . esc_attr( $command ) . '"';
	$attrs[] = $i18n404tools_modal_config['data_plugin'] . '="' . esc_attr( $plugin ) . '"';
	$attrs[] = $i18n404tools_modal_config['data_step'] . '="' . esc_attr( $step ) . '"';

	return implode( ' ', $attrs );
}
