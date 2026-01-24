// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

/**
 * JavaScript plural forms test fixture.
 *
 * @package I18n_404_Tools_Tests
 */

const { _n, _nx } = wp.i18n;

// Simple plural.
_n( 'One JS item', 'Multiple JS items', count, 'testdomain' );

// Plural with context.
_nx( 'One JS post', 'Multiple JS posts', count, 'blog posts', 'testdomain' );

// Plural on multiple lines.
_n(
	'Singular JS form',
	'Plural JS form',
	count,
	'testdomain'
);
