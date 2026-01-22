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

// Define plugin directory used by command classes during tests.
if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}

// Ensure base directories exist.
if ( ! is_dir( WP_CONTENT_DIR ) ) {
	mkdir( WP_CONTENT_DIR, 0755, true );
}
if ( ! is_dir( WP_PLUGIN_DIR ) ) {
	mkdir( WP_PLUGIN_DIR, 0755, true );
}

// Minimal WordPress function shims for tests.
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $value ) {
		return is_string( $value ) ? trim( $value ) : $value;
	}
}

if ( ! function_exists( 'wp_mkdir_p' ) ) {
	function wp_mkdir_p( $path ) {
		return is_dir( $path ) ? true : mkdir( $path, 0755, true );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = null ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = null ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	function esc_attr__( $text, $domain = null ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return $text;
	}
}

if ( ! function_exists( '_n' ) ) {
	function _n( $single, $plural, $number, $domain = null ) {
		return ( 1 === absint( $number ) ) ? $single : $plural;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ) {
		return (int) max( 0, $maybeint );
	}
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
