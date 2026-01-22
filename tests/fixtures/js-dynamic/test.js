/**
 * JavaScript concatenation and expressions.
 *
 * @package I18n_404_Tools_Tests
 */

const { __, _n } = wp.i18n;

// String concatenation with + (should NOT be extracted - not static).
__( 'Part 1' + ' Part 2', 'testdomain' );

// Variable concatenation (should NOT be extracted).
const prefix = 'Hello';
__( prefix + ' World', 'testdomain' );

// Expression in argument (should NOT be extracted).
__( someFunction(), 'testdomain' );

// Ternary in string argument (should NOT be extracted).
__( condition ? 'Yes' : 'No', 'testdomain' );

// Array element (should NOT be extracted).
const strings = ['String 1', 'String 2'];
__( strings[0], 'testdomain' );

// Object property (should NOT be extracted).
const obj = { text: 'Object string' };
__( obj.text, 'testdomain' );
