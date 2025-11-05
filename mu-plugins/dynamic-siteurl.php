<?php
/**
 * Dynamic siteurl/home for Codespaces/devcontainers.
 *
 * Sets siteurl and home at runtime based on the request Host and forwarded proto.
 * Useful in Codespaces where the public host encodes the port (example-8080.app.github.dev).
 */

if ( ! defined( 'WPINC' ) && ! defined( 'ABSPATH' ) ) {
    // mu-plugins are loaded by WP, but if this ever runs standalone, bail.
    // Do nothing; safer to let WP load normally.
}

$detected_proto = 'http';
if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
  || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
  || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') ) {
    $detected_proto = 'https';
}

if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
    $site = $detected_proto . '://' . $_SERVER['HTTP_HOST'];

    add_filter( 'option_siteurl', function( $value ) use ( $site ) {
        return $site;
    } );

    add_filter( 'option_home', function( $value ) use ( $site ) {
        return $site;
    } );
}
