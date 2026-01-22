<?php
/**
 * Translator comments test fixture.
 *
 * @package I18n_404_Tools_Tests
 */

// translators: This is a translator comment.
__( 'String with comment', 'testdomain' );

/* translators: Multi-line translator comment
 * that spans multiple lines.
 */
__( 'String with multiline comment', 'testdomain' );

// translators: %s is the username.
sprintf( __( 'Hello, %s!', 'testdomain' ), $username );

// Regular comment (should NOT be extracted).
__( 'String without translator prefix', 'testdomain' );

/* translators: 1: post title, 2: author name */
sprintf( __( '%1$s by %2$s', 'testdomain' ), $title, $author );
