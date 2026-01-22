<?php
/**
 * PHPUnit bootstrap file for i18n-404-tools tests.
 *
 * @package I18n_404_Tools
 */

/*
 * SPDX-FileCopyrightText: 2026 Eric van der Vlist <vdv@dyomedea.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

// Load Composer autoloader.
require_once __DIR__ . '/../vendor/autoload.php';

// Define test constants.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/tmp/wp-content' );
}

// Load plugin files needed for testing.
require_once __DIR__ . '/../i18n-404-tools/admin/class-i18n-wp-cli-compat.php';
require_once __DIR__ . '/../i18n-404-tools/admin/class-i18n-wp-cli-utils.php';
require_once __DIR__ . '/../i18n-404-tools/admin/class-i18n-extractor.php';

// Create output directory for test results.
$output_dir = __DIR__ . '/output';
if ( ! is_dir( $output_dir ) ) {
	mkdir( $output_dir, 0755, true );
}
