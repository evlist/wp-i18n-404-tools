// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

/**
 * JavaScript template literals test fixture.
 *
 * @package I18n_404_Tools_Tests
 */

const { __, _x, _n } = wp.i18n;

// Template literal without interpolation (static - should be extracted).
__( `Static template literal`, 'testdomain' );

// Template literal with variable (should NOT be extracted - dynamic).
const name = 'World';
__( `Hello ${name}`, 'testdomain' );

// Template literal with expression (should NOT be extracted).
__( `Result: ${1 + 1}`, 'testdomain' );

// Template literal multiline (static).
__( `This is a
multiline template
literal`, 'testdomain' );

// Template literal with special chars (static).
__( `Quote: " and apostrophe: '`, 'testdomain' );

// Template literal with backtick escaped (static).
__( `Backtick: \``, 'testdomain' );

// Template literal with tab.
__( `Column1\tColumn2`, 'testdomain' );

// Template literal with newline.
__( `Line1\nLine2`, 'testdomain' );

// Nested template (should NOT be extracted).
__( `Outer ${`Inner`}`, 'testdomain' );
