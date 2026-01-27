<?php

// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

/**
 * Tests for JSON command helper methods.
 *
 * @package I18n_404_Tools
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../i18n-404-tools/admin/class-i18n-404-generate-json-command.php';

/**
 * Expose protected helpers for testing.
 */
class Testable_JSON_Command extends I18N_404_Generate_JSON_Command {
	public function expose_has_javascript_strings() {
		return $this->has_javascript_strings();
	}

	public function expose_get_json_status( $po_files ) {
		return $this->get_json_status( $po_files );
	}
}

class GenerateJsonCommandUnitTest extends TestCase {
	private $plugin_slug;
	private $plugin_dir;
	private $languages_dir;
	private $domain;
	private $plugin_folder;

	       protected function setUp(): void {
		       $unique            = 'jsoncmd-' . uniqid();
		       $this->plugin_folder = $unique;
		       $this->plugin_slug = $unique; // On passe le slug dossier, pas le chemin relatif du fichier principal
		       $this->plugin_dir  = WP_PLUGIN_DIR . '/' . $this->plugin_folder;
		       $this->languages_dir = $this->plugin_dir . '/languages';
		       $this->domain      = $unique;

		       if ( ! is_dir( $this->languages_dir ) ) {
			       mkdir( $this->languages_dir, 0755, true );
		       }

		       file_put_contents( $this->plugin_dir . '/' . $this->plugin_folder . '.php', "<?php\n// test plugin stub\n" );
	       }

	protected function tearDown(): void {
		if ( is_dir( $this->plugin_dir ) ) {
			$this->remove_dir( $this->plugin_dir );
		}
	}

	       private function make_command() {
		       return new Testable_JSON_Command( $this->plugin_slug );
	       }

	private function remove_dir( $dir ) {
		$items = scandir( $dir );
		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}
			$path = $dir . '/' . $item;
			if ( is_dir( $path ) ) {
				$this->remove_dir( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}

	public function test_get_json_status_marks_existing_and_outdated() {
		$po_file = $this->languages_dir . '/' . $this->domain . '-fr_FR.po';
		file_put_contents( $po_file, 'msgid ""\nmsgstr ""' );

		$old_json = $this->languages_dir . '/' . $this->domain . '-fr_FR-oldhash.json';
		$new_json = $this->languages_dir . '/' . $this->domain . '-fr_FR-newhash.json';
		file_put_contents( $old_json, '{}' );
		file_put_contents( $new_json, '{}' );

		$now = time();
		touch( $po_file, $now );
		touch( $old_json, $now - 10 );
		touch( $new_json, $now + 10 );

		$command = $this->make_command();
		$status  = $command->expose_get_json_status( array( $po_file ) );

		$this->assertEqualsCanonicalizing( array( $old_json, $new_json ), $status['existing'] );
		$this->assertEquals( array( $po_file ), $status['outdated'] );
	}

	public function test_get_json_status_marks_missing_json_as_outdated() {
		$po_file = $this->languages_dir . '/' . $this->domain . '-fr_FR.po';
		file_put_contents( $po_file, 'msgid ""\nmsgstr ""' );

		$command = $this->make_command();
		$status  = $command->expose_get_json_status( array( $po_file ) );

		$this->assertEmpty( $status['existing'] );
		$this->assertEquals( array( $po_file ), $status['outdated'] );
	}
}
