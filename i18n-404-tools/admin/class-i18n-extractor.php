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
				* Ensure vendor autoload and local WP_CLI\I18n classes are loaded once.
				*/
	private function ensure_loaded() {
		if ( $this->loaded ) {
			return;
		}

		// Load Composer's autoloader from the standard plugin vendor directory.
		require_once dirname( __DIR__ ) . '/vendor/autoload.php';

		// Register a local PSR-4 autoloader for WP_CLI\I18n classes from admin/wp-cli/src.
		$src_dir = __DIR__ . '/wp-cli/src';
		spl_autoload_register(
			function ( $class ) use ( $src_dir ) {
				if ( 0 === strpos( $class, 'WP_CLI\\I18n\\' ) ) {
					$relative_class = substr( $class, strlen( 'WP_CLI\\I18n\\' ) );
					$file = $src_dir . '/' . str_replace( '\\', '/', $relative_class ) . '.php';
					if ( file_exists( $file ) ) {
						require_once $file;
						return true;
					}
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
