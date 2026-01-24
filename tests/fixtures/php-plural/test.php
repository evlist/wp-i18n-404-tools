<?php

// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

/**
 * Plural forms test fixture.
 *
 * @package I18n_404_Tools_Tests
 */

// Simple plural.
_n( 'One item', 'Multiple items', $count, 'testdomain' );

// Plural with context.
_nx( 'One post', 'Multiple posts', $count, 'blog posts', 'testdomain' );

// Escaped plural.
esc_html( _n( 'One comment', 'Multiple comments', $count, 'testdomain' ) );

// Number formatting.
_n_noop( 'One user', 'Multiple users', 'testdomain' );

// Plural on multiple lines.
_n(
	'Singular form',
	'Plural form',
	$count,
	'testdomain'
);
