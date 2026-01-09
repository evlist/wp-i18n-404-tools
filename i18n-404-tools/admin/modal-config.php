<?php
/**
 * Modal dialog configuration.
 *
 * @package I18n_404_Tools
 */

// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>.
//
// SPDX-License-Identifier: GPL-3.0-or-later

global $i18n404tools_modal_config;

$i18n404tools_modal_config = array(
	// CSS classes.
	'action_class'  => 'i18n-404-tools-action',
	'overlay_class' => 'i18n-404-tools-modal-overlay',
	'content_class' => 'i18n-404-tools-modal-content',
	'close_class'   => 'i18n-404-tools-modal-close',
	'body_class'    => 'i18n-404-tools-modal-body',

	// Data attribute names (prefix all!).
	'data_command'  => 'data-i18n-404-tools-command',
	'data_plugin'   => 'data-i18n-404-tools-plugin',
	'data_step'     => 'data-i18n-404-tools-step',
);
