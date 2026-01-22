<?php
/**
 * PHP string concatenation and complex expressions.
 *
 * @package I18n_404_Tools_Tests
 */

// Concatenation with dot operator (should NOT be extracted - not static).
__( 'Part 1' . ' Part 2', 'testdomain' );

// String with newline escape sequence.
__( "Line 1\nLine 2", 'testdomain' );

// String with tab escape sequence.
__( "Column 1\tColumn 2", 'testdomain' );

// String with backslash.
__( 'Path\\to\\file', 'testdomain' );

// String with dollar sign.
__( 'Price: $10', 'testdomain' );

// String with null byte (rare).
__( "Null\0byte", 'testdomain' );

// Unicode escape sequence.
__( "Unicode: \u{1F600}", 'testdomain' );

// Octal escape sequence.
__( "Octal: \101", 'testdomain' );

// Hex escape sequence.
__( "Hex: \x41", 'testdomain' );

// Mixed quotes and escapes.
__( "He said \"Hello\" and left", 'testdomain' );
__( 'She said \'Hi\' and smiled', 'testdomain' );

// CRLF line endings.
__( "Windows\r\nline ending", 'testdomain' );
