<?php
/**
 * DEV ONLY wp-config for Codespaces / devcontainer.
 *
 * WARNING: This file is intended for Codespaces/devcontainer use only.
 * It will attempt to detect a Codespace/devcontainer environment before
 * forcing WP_HOME / WP_SITEURL. Do NOT use in production.
 */

/* Detect whether we're running in a Codespace / devcontainer.
 * We check:
 *  - the CODESPACE_NAME env var (set in Codespaces), OR
 *  - whether the repository is mounted under /workspaces (common in Codespaces),
 *  - OR the presence of the /.devcontainer folder path in the current dir.
 */
$in_devcontainer = false;
if (getenv('CODESPACE_NAME')) {
    $in_devcontainer = true;
} elseif (strpos(__DIR__, '/workspaces/') !== false) {
    $in_devcontainer = true;
} elseif (file_exists(__DIR__ . '/../.devcontainer')) {
    $in_devcontainer = true;
}

if ($in_devcontainer) {
    // ---- Normalise proxy/request headers so WP does not append :8080 ----
    // If the proxy indicates https, make PHP treat the request as secure.
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $xfp = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
        if ($xfp === 'https') {
            $_SERVER['HTTPS'] = 'on';
            $_SERVER['SERVER_PORT'] = '443';
        } else {
            $_SERVER['HTTPS'] = 'off';
            $_SERVER['SERVER_PORT'] = '80';
        }
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';
    }

    // Strip any trailing :port from HTTP_HOST to avoid WordPress adding it back.
    if (!empty($_SERVER['HTTP_HOST'])) {
        $_SERVER['HTTP_HOST'] = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']);
    }

    // Detect scheme (prefer X-Forwarded-Proto / X-Forwarded-SSL set by proxy)
    $scheme = 'http';
    if (
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ) {
        $scheme = 'https';
    }

    // Build host-based site URL from the incoming request host
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $public_site = $scheme . '://' . $host;

    // Force WP to use the request host (prevents installer / redirects from using :8080)
    define('WP_HOME', $public_site);
    define('WP_SITEURL', $public_site);
}

/* -------------------------
 * Database settings (match .devcontainer/docker-compose.yml)
 * ------------------------- */
define('DB_NAME', 'wordpress');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'wordpress');
define('DB_HOST', 'db:3306');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

/* Authentication unique keys and salts (dev placeholders) */
define('AUTH_KEY',         'dev-auth-key');
define('SECURE_AUTH_KEY',  'dev-secure-auth-key');
define('LOGGED_IN_KEY',    'dev-logged-in-key');
define('NONCE_KEY',        'dev-nonce-key');
define('AUTH_SALT',        'dev-auth-salt');
define('SECURE_AUTH_SALT', 'dev-secure-auth-salt');
define('LOGGED_IN_SALT',   'dev-logged-in-salt');
define('NONCE_SALT',       'dev-nonce-salt');

$table_prefix = 'wp_';
define('WP_DEBUG', true);

/* Absolute path and bootstrap. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/..' . '/' );
}
require_once ABSPATH . 'wp-settings.php';
