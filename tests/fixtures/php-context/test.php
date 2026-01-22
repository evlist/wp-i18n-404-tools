<?php
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
