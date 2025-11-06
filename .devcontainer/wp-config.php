<?php
/**
 * DEV ONLY wp-config for Codespaces / devcontainer.
 *
 * WARNING: This file is intended for Codespaces/devcontainer use only.
 * It will attempt to detect a Codespace/devcontainer environment before
 * forcing WP_HOME / WP_SITEURL. Do NOT use in production.
 */
/**
 * Simple devcontainer-only normalization and redirect.
 *
 * Behavior (dirty, explicit):
 * - Only runs when an explicit environment variable is present (DEVCONTAINER or CODESPACE_NAME).
 * - If HTTP_X_FORWARDED_HOST looks like a Codespaces tunnel host (*.app.github.dev) we build a
 *   base URL using HTTP_X_SCHEME or HTTP_X_FORWARDED_PROTO and DO NOT append any port.
 * - Sets WP_HOME and WP_SITEURL if not already defined.
 *
 * Keep this intentionally small and noisy so it only affects devcontainers when you opt in.
 */

$in_devcontainer = (bool) (getenv('DEVCONTAINER') || getenv('CODESPACE_NAME') || getenv('GITHUB_CODESPACES'));

if ($in_devcontainer) {
    $xf_host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? null;
    $xf_scheme = $_SERVER['HTTP_X_SCHEME'] ?? ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null);

    // Basic sanity check: only trust forwarded host values that look like Codespaces tunnels
    // e.g. something-xxxx-8080.app.github.dev or something-xxxx-443.app.github.dev
    $is_codespace_host = false;
    if ($xf_host) {
        // If there are multiple hosts in the header, take the first one
        $xf_host = trim(explode(',', $xf_host)[0]);

        // strip any trailing port if present (we will not append a port later)
        $xf_host_noport = preg_replace('/:\d+$/', '', $xf_host);

        // crude but effective check for Codespaces tunnel host
        if (preg_match('/\.app\.github\.dev$/i', $xf_host_noport)) {
            $is_codespace_host = true;
            $xf_host = $xf_host_noport;
        }
    }

    if ($is_codespace_host && $xf_scheme) {
        // prefer explicit scheme header, fall back to https if absent
        $scheme = strtolower(trim(explode(',', $xf_scheme)[0]));
        if ($scheme !== 'http' && $scheme !== 'https') {
            // if scheme looks bogus, default to https for tunnels
            $scheme = 'https';
        }

        $public_base = $scheme . '://' . $xf_host;

        // Only set WP_HOME/WP_SITEURL if not already defined
        if (!defined('WP_HOME')) {
            define('WP_HOME', $public_base);
        }
        if (!defined('WP_SITEURL')) {
            define('WP_SITEURL', $public_base);
        }

        // Normalize a couple server vars so WP/core/plugins expecting them behave
        $_SERVER['SERVER_PORT'] = ($scheme === 'https') ? '443' : '80';
        if ($scheme === 'https') {
            $_SERVER['HTTPS'] = 'on';
        } else {
            unset($_SERVER['HTTPS']);
        }
    }
}

// Small helper flag other code can test
if (!defined('WP_IN_DEVCONTAINER')) {
    define('WP_IN_DEVCONTAINER', $in_devcontainer);
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
