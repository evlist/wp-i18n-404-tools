<?php
/**
 * Safe wrapper around vendored WP-CLI i18n commands.
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

require_once __DIR__ . '/class-i18n-wp-cli-compat.php';

/**
 * Executes vendored WP-CLI i18n commands in-process.
 */
class I18N_404_Extractor {
	/**
	 * Whether vendor classes are loaded.
	 *
	 * @var bool
	 */
	private $loaded = false;

	/**
	 * Ensure vendor autoload and all command classes are loaded once.
	 */
	private function ensure_loaded() {
		if ( $this->loaded ) {
			return;
		}

		// Load WP_CLI\Utils functions that are needed by vendored code.
		require_once __DIR__ . '/class-i18n-wp-cli-utils.php';

		// Try to locate Composer's autoloader from multiple possible locations.
		// Plugin vendor (gettext/gettext) is searched FIRST for priority.
		$autoload_paths = array(
			dirname( __DIR__ ) . '/vendor/autoload.php',     // Plugin vendor (i18n-404-tools/vendor) - FIRST PRIORITY.
			dirname( __DIR__, 2 ) . '/vendor/autoload.php',  // Repo root when plugin lives in /i18n-404-tools/.
			dirname( __DIR__, 3 ) . '/vendor/autoload.php',  // When plugin is placed under wp-content/plugins/.
			dirname( __DIR__, 4 ) . '/vendor/autoload.php',  // Safety net for odd layouts.
			dirname( __DIR__, 8 ) . '/vendor/autoload.php',  // Devcontainer path back to repo root vendor/.
			( defined( 'ABSPATH' ) ? ABSPATH . '../vendor/autoload.php' : null ),
			( defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR . '/vendor/autoload.php' : null ),
		);

		$autoload_paths = array_filter( array_unique( $autoload_paths ) );

		$found_autoload = null;
		foreach ( $autoload_paths as $autoload ) {
			if ( file_exists( $autoload ) ) {
				require_once $autoload;
				$found_autoload = $autoload;
				break;
			}
		}

		$stubs = __DIR__ . '/wp-cli/gettext-stubs.php';

		// Provide an autoloader so Gettext classes resolve to our stubs when the real dependency is absent.
		$load_gettext_stubs = function () use ( $stubs ) {
			if ( file_exists( $stubs ) ) {
				require_once $stubs;
				return class_exists( '\\Gettext\\Translations', false );
			}
			return false;
		};

		spl_autoload_register(
			function ( $class ) use ( $load_gettext_stubs ) {
				if ( 0 === strpos( $class, 'Gettext\\' ) ) {
					return $load_gettext_stubs();
				}
				return false;
			}
		);

		// Fallback: manually include Gettext\Translations if autoloaders were not found or class is still missing.
		if ( ! class_exists( '\\Gettext\\Translations' ) ) {
			$load_gettext_stubs();

			$vendor_roots = array();
			if ( $found_autoload ) {
				$vendor_roots[] = dirname( $found_autoload, 2 );
			}
			foreach ( $autoload_paths as $autoload ) {
				$vendor_roots[] = dirname( $autoload, 2 );
			}
			$vendor_roots   = array_filter( array_unique( $vendor_roots ) );
			$translations_paths = array();
			foreach ( $vendor_roots as $root ) {
				$translations_paths[] = $root . '/gettext/gettext/src/Translations.php';
				$translations_paths[] = $root . '/vendor/gettext/gettext/src/Translations.php';
			}
			foreach ( $translations_paths as $file ) {
				if ( file_exists( $file ) ) {
					require_once $file;
					break;
				}
			}
		}

		// Register PSR-4 autoloader for WP_CLI\I18n classes from vendored code.
		$src_dir = __DIR__ . '/wp-cli/src';
		spl_autoload_register(
			function ( $class ) use ( $src_dir ) {
				// Handle WP_CLI\I18n\* classes.
				if ( 0 === strpos( $class, 'WP_CLI\\I18n\\' ) ) {
					$relative_class = substr( $class, strlen( 'WP_CLI\\I18n\\' ) );
					$file            = $src_dir . '/' . str_replace( '\\', '/', $relative_class ) . '.php';
					if ( file_exists( $file ) ) {
						require_once $file;
						return true;
					}
				}

				// Handle WP_CLI\Utils - create it dynamically from our global WP_CLI_Utils.
				if ( 'WP_CLI\\Utils' === $class ) {
					// Create an alias to the global WP_CLI_Utils class.
					class_alias( 'WP_CLI_Utils', 'WP_CLI\\Utils' );
					return true;
				}

				// Handle WP_CLI\ExitException - create it dynamically from our global class.
				if ( 'WP_CLI\\ExitException' === $class ) {
					class_alias( 'WP_CLI_ExitException', 'WP_CLI\\ExitException' );
					return true;
				}

				// Handle WP_CLI_Command from our shim.
				if ( 'WP_CLI_Command' === $class || 'WP_CLI\\Command' === $class ) {
					if ( ! class_exists( 'WP_CLI_Command' ) ) {
						class_alias( 'WP_CLI_Command', 'WP_CLI\\Command' );
					}
					return true;
				}

				return false;
			}
		);

		$this->loaded = true;
	}
	/**
	 * Run the MakePot command.
	 *
	 * @param string $source      Source directory.
	 * @param string $destination Destination .pot file path.
	 * @param string $domain      Text domain.
	 * @return array Result array with success flag and output.
	 */
	public function generate_pot( $source, $destination, $domain ) {
		$this->ensure_loaded();

		return $this->run_command(
			function () use ( $source, $destination, $domain ) {
				$command = new \WP_CLI\I18n\MakePotCommand();
				$command->__invoke(
					array( $source, $destination ),
					array( 'domain' => $domain )
				);
			}
		);
	}

	/**
	 * Run the MakeJson command.
	 *
	 * @param string $source     Source directory with .po files.
	 * @param array  $assoc_args Additional associative args.
	 * @return array Result array with success flag and output.
	 */
	public function generate_json( $source, $assoc_args = array() ) {
		$this->ensure_loaded();

		$defaults   = array( 'purge' => false );
		$assoc_args = array_merge( $defaults, $assoc_args );

		return $this->run_command(
			function () use ( $source, $assoc_args ) {
				$command = new \WP_CLI\I18n\MakeJsonCommand();
				$command->__invoke( array( $source ), $assoc_args );
			}
		);
	}

	/**
	 * Execute a vendored command with captured logs.
	 *
	 * @param callable $callback Command callback.
	 * @return array Result payload.
	 */
	private function run_command( $callback ) {
		$logger = new I18n_WP_CLI_Logger();
		\WP_CLI::set_logger( $logger );

		try {
			$callback();
			return array(
				'success' => true,
				'output'  => $logger->to_string(),
				'logs'    => $logger->export(),
			);
		} catch ( WP_CLI_ExitException $e ) {
			return array(
				'success' => false,
				'output'  => $logger->to_string(),
				'error'   => $e->getMessage(),
				'logs'    => $logger->export(),
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'output'  => $logger->to_string(),
				'error'   => $e->getMessage(),
				'logs'    => $logger->export(),
			);
		} catch ( \Throwable $e ) {
			return array(
				'success' => false,
				'output'  => $logger->to_string(),
				'error'   => $e->getMessage(),
				'logs'    => $logger->export(),
			);
		}
	}
}
