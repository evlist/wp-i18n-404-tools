/**
 * Simple JavaScript translations test fixture.
 *
 * @package I18n_404_Tools_Tests
 */

// wp.i18n functions
const { __, _x, _n, _nx } = wp.i18n;

// Simple string.
__( 'JavaScript string', 'testdomain' );

// Double quotes.
__( "Double quoted JS string", 'testdomain' );

// Template literals (should NOT be extracted).
__( `Template literal ${variable}`, 'testdomain' );

// Concatenation (should NOT be extracted).
__( 'Part 1' + 'Part 2', 'testdomain' );

// Multiple on same line.
__( 'First JS', 'testdomain' ); __( 'Second JS', 'testdomain' );

// Escaped quotes.
__( 'String with \'quotes\'', 'testdomain' );
__( "String with \"quotes\"", 'testdomain' );

// Wrong domain.
__( 'Wrong JS domain', 'wrongdomain' );
