<?php

// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

/**
 * Context strings test fixture.
 *
 * @package I18n_404_Tools_Tests
 */

// String with context.
_x( 'Post', 'noun', 'testdomain' );
_x( 'Post', 'verb', 'testdomain' );

// Context with escape.
esc_html_x( 'Draft', 'post status', 'testdomain' );

// Plural with context.
_nx( 'One file', 'Multiple files', $count, 'file manager', 'testdomain' );

// Context on multiple lines.
_x(
	'Settings',
	'menu item',
	'testdomain'
);
