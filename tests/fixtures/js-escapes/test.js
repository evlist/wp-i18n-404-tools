// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

/**
 * JavaScript string escapes and special characters.
 *
 * @package I18n_404_Tools_Tests
 */

const { __, _x } = wp.i18n;

// String with newline.
__( 'Line 1\nLine 2', 'testdomain' );

// String with tab.
__( 'Column 1\tColumn 2', 'testdomain' );

// String with carriage return.
__( 'Text\rOverwrite', 'testdomain' );

// String with backslash.
__( 'Path\\to\\file', 'testdomain' );

// String with unicode escape.
__( 'Unicode: \u0041', 'testdomain' );

// String with hex escape.
__( 'Hex: \x41', 'testdomain' );

// String with emoji (unicode).
__( 'Emoji: \u{1F600}', 'testdomain' );

// Mixed single and double quotes.
__( "She said \"hello\"", 'testdomain' );
__( 'He said \'hi\'', 'testdomain' );

// Escaped backslash.
__( 'Backslash: \\', 'testdomain' );

// Null character.
__( 'Null\0char', 'testdomain' );

// CRLF.
__( 'Windows\r\nLine', 'testdomain' );

// Form feed.
__( 'Form\fFeed', 'testdomain' );

// Vertical tab.
__( 'Vertical\vTab', 'testdomain' );
