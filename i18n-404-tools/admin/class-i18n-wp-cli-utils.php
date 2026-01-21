<?php
/**
 * WP-CLI Utils namespace shim for vendored i18n commands.
 *
 * @package I18n_404_Tools
 */

/*
 * SPDX-FileCopyrightText: 2026 Eric van der Vlist <vdv@dyomedea.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace WP_CLI\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse shell array options (simplified for in-process use).
 *
 * @param array $assoc_args      Associative args.
 * @param array $array_arguments Keys that may contain JSON arrays.
 * @return array
 */
function parse_shell_arrays( $assoc_args, $array_arguments = array() ) {
	foreach ( $array_arguments as $key ) {
		if ( isset( $assoc_args[ $key ] ) && is_string( $assoc_args[ $key ] ) ) {
			$decoded = json_decode( $assoc_args[ $key ], true );
			if ( null !== $decoded ) {
				$assoc_args[ $key ] = $decoded;
			}
		}
	}
	return $assoc_args;
}

/**
 * Get a flag value from args.
 *
 * @param array  $assoc_args Arguments.
 * @param string $key        Key to read.
 * @param mixed  $default    Default value.
 * @return mixed
 */
function get_flag_value( $assoc_args, $key, $default = null ) {
	if ( array_key_exists( $key, $assoc_args ) ) {
		return $assoc_args[ $key ];
	}
	return $default;
}

/**
 * Safe basename helper.
 *
 * @param string $path Path.
 * @return string
 */
function basename( $path ) {
	$path = rtrim( (string) $path, '/\\' );
	return \basename( $path );
}

/**
 * Normalize path separators.
 *
 * @param string $path Path.
 * @return string
 */
function normalize_path( $path ) {
	return str_replace( '\\', '/', $path );
}

/**
 * Simple pluralizer.
 *
 * @param string $word   Word to pluralize.
 * @param int    $count  Count.
 * @return string
 */
function pluralize( $word, $count ) {
	return ( 1 === (int) $count ) ? $word : $word . 's';
}

/**
 * HTTP request wrapper using WordPress HTTP API.
 *
 * @param string            $method  HTTP method.
 * @param string            $url     URL.
 * @param string|array|null $data    Optional body.
 * @param array             $headers Optional headers.
 * @param array             $options Optional options (timeout in milliseconds accepted).
 * @return object Response-like object with status_code, headers, body, url.
 */
function http_request( $method, $url, $data = null, $headers = array(), $options = array() ) {
	$args = array(
		'headers' => $headers,
		'body'    => $data,
		'method'  => $method,
	);

	if ( isset( $options['timeout'] ) ) {
		$args['timeout'] = max( 1, (int) ceil( $options['timeout'] / 1000 ) );
	}

	$response = wp_remote_request( $url, $args );

	if ( is_wp_error( $response ) ) {
		return (object) array(
			'status_code' => 500,
			'headers'     => array(),
			'body'        => $response->get_error_message(),
			'url'         => $url,
		);
	}

	return (object) array(
		'status_code' => wp_remote_retrieve_response_code( $response ),
		'headers'     => wp_remote_retrieve_headers( $response ),
		'body'        => wp_remote_retrieve_body( $response ),
		'url'         => $url,
	);
}
