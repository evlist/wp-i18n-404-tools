<?php
/**
 * File: gettext-stubs.php
 *
 * Minimal stubs for WP-CLI i18n compatibility.
 *
 * @package i18n-404-tools
 * @author  Eric van der Vlist <vdv@dyomedea.com>
 * SPDX-FileCopyrightText: 2026 Eric van der Vlist <vdv@dyomedea.com>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Stubs minimalistes pour la compatibilité WP-CLI i18n.

require_once __DIR__ . '/stubs/translation.php';
require_once __DIR__ . '/stubs/translations.php';
require_once __DIR__ . '/stubs/merge.php';
require_once __DIR__ . '/stubs/parsed_comment.php';
require_once __DIR__ . '/stubs/po.php';
