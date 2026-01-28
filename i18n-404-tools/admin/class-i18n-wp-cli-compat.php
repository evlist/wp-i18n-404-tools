<?php
/**
 * WP-CLI compatibility layer for bundled i18n commands.
 *
 * @package I18n_404_Tools
 */

/*
 * SPDX-FileCopyrightText: 2026 Eric van der Vlist <vdv@dyomedea.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Provide a stable WP-CLI version string expected by bundled commands.
if ( ! defined( 'WP_CLI_VERSION' ) ) {
	define( 'WP_CLI_VERSION', '2.11.0-stub' );
}
// Some vendored code may look for a namespaced constant inside WP_CLI\I18n.
if ( ! defined( 'WP_CLI\I18n\WP_CLI_VERSION' ) ) {
	define( 'WP_CLI\I18n\WP_CLI_VERSION', WP_CLI_VERSION );
}

// Make sure we're only defining classes once.
if ( class_exists( 'WP_CLI_ExitException' ) ) {
	return;
}

/**
 * Exception used by WP_CLI::error.
 */
class WP_CLI_ExitException extends \RuntimeException {
}

/**
 * Minimal WP_CLI facade (partial).
 */
class WP_CLI {
	/**
	 * Logger instance.
	 *
	 * @var I18n_WP_CLI_Logger|null
	 */
	protected static $logger = null;

	/**
	 * Assign a logger instance.
	 *
	 * @param I18n_WP_CLI_Logger $logger Logger instance.
	 */
	public static function set_logger( I18n_WP_CLI_Logger $logger ) {
		self::$logger = $logger;
	}

	/**
	 * Get the logger instance (lazy).
	 *
	 * @return I18n_WP_CLI_Logger
	 */
	protected static function logger() {
		if ( ! self::$logger ) {
			self::$logger = new I18n_WP_CLI_Logger();
		}

		return self::$logger;
	}

	/**
	 * Log a debug message (disabled).
	 *
	 * @param string      $message Message.
	 * @param string|null $group   Optional group name.
	 */
	public static function debug( $message, $group = null ) {
		// Debug messages disabled for cleaner UI output.
	}

	/**
	 * Log an info message.
	 *
	 * @param string $message Message.
	 */
	public static function log( $message ) {
		self::logger()->info( $message );
	}

	/**
	 * Alias for log().
	 *
	 * @param string $message Message.
	 */
	public static function line( $message ) {
		self::log( $message );
	}

	/**
	 * Log a warning message.
	 *
	 * @param string $message Message.
	 */
	public static function warning( $message ) {
		self::logger()->warning( $message );
	}

	/**
	 * Log a success message.
	 *
	 * @param string $message Message.
	 */
	public static function success( $message ) {
		self::logger()->success( $message );
	}

	/**
	 * Raise an error and stop execution.
	 *
	 * @param string $message Message.
	 * @throws WP_CLI_ExitException When an error occurs.
	 */
	public static function error( $message ) {
		self::logger()->error( $message );
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Message is passed to logger and exception only.
		throw new WP_CLI_ExitException( $message );
	}

	/**
	 * Expose logger for consumers.
	 *
	 * @return I18n_WP_CLI_Logger
	 */
	public static function get_logger() {
		return self::logger();
	}
}

/**
 * Base command placeholder.
 */
class WP_CLI_Command {
}

/**
 * Lightweight logger used by the WP_CLI shim.
 */
class I18n_WP_CLI_Logger {
	/**
	 * Logged entries.
	 *
	 * @var array
	 */
	private $entries = array();

	/**
	 * Add a debug log entry.
	 *
	 * @param string      $message Message to log.
	 * @param string|null $group   Optional group name.
	 */
	public function debug( $message, $group = null ) {
		$this->add( 'debug', $message, $group );
	}

	/**
	 * Add an info log entry.
	 *
	 * @param string $message Message to log.
	 */
	public function info( $message ) {
		$this->add( 'info', $message );
	}

	/**
	 * Add a warning log entry.
	 *
	 * @param string $message Message to log.
	 */
	public function warning( $message ) {
		$this->add( 'warning', $message );
	}

	/**
	 * Add a success log entry.
	 *
	 * @param string $message Message to log.
	 */
	public function success( $message ) {
		$this->add( 'success', $message );
	}

	/**
	 * Add an error log entry.
	 *
	 * @param string $message Message to log.
	 */
	public function error( $message ) {
		$this->add( 'error', $message );
	}

	/**
	 * Export raw entries.
	 *
	 * @return array
	 */
	public function export() {
		return $this->entries;
	}

	/**
	 * Stringify entries for UI display.
	 *
	 * @return string
	 */
	public function to_string() {
		$lines = array();
		foreach ( $this->entries as $entry ) {
			$prefix  = strtoupper( $entry['level'] );
			$lines[] = $prefix . ': ' . $entry['message'];
		}
		return implode( "\n", $lines );
	}

	/**
	 * Store a log entry.
	 *
	 * @param string      $level   Level name.
	 * @param string      $message Message.
	 * @param string|null $group   Optional group name.
	 */
	private function add( $level, $message, $group = null ) {
		$this->entries[] = array(
			'level'   => $level,
			'group'   => $group,
			'message' => $message,
		);
	}
}

// phpcs:enable Generic.Files.OneObjectStructurePerFile.MultipleFound
