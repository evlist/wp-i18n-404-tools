<?php

// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

/**
 * Simple string translations test fixture.
 *
 * @package I18n_404_Tools_Tests
 */

// Single quotes.
__( 'Simple string', 'testdomain' );

// Double quotes.
__( "Double quoted string", 'testdomain' );

// With variables (should NOT be extracted).
$var = 'dynamic';
__( $var, 'testdomain' );

// Multiple on same line.
__( 'First', 'testdomain' ); __( 'Second', 'testdomain' );

// Escaped quotes.
__( "String with \"quotes\"", 'testdomain' );
__( 'String with \'quotes\'', 'testdomain' );

// _e() function.
_e( 'Echo string', 'testdomain' );

// esc_html__() function.
esc_html__( 'Escaped HTML string', 'testdomain' );

// esc_attr__() function.
esc_attr__( 'Escaped attribute string', 'testdomain' );

// Wrong domain (should NOT be extracted).
__( 'Wrong domain', 'wrongdomain' );

// No domain (should NOT be extracted).
__( 'No domain' );
