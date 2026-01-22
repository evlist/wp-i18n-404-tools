<?php
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

	public function expose_find_js_files( $dir ) {
		return $this->find_js_files( $dir );
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

	protected function setUp(): void {
		$unique            = 'jsoncmd-' . uniqid();
		$this->plugin_slug = $unique . '/' . $unique . '.php';
		$this->plugin_dir  = WP_PLUGIN_DIR . '/' . dirname( $this->plugin_slug );
		$this->languages_dir = $this->plugin_dir . '/languages';
		$this->domain      = basename( $this->plugin_slug, '.php' );

		if ( ! is_dir( $this->languages_dir ) ) {
			mkdir( $this->languages_dir, 0755, true );
		}

		file_put_contents( $this->plugin_dir . '/' . basename( $this->plugin_slug ), "<?php\n// test plugin stub\n" );
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

	public function test_has_javascript_strings_detects_wp_i18n_usage() {
		$js_dir = $this->plugin_dir . '/assets';
		mkdir( $js_dir, 0755, true );
		file_put_contents( $js_dir . '/app.js', "const { __ } = wp.i18n; __( 'Hi', '" . $this->domain . "' );" );

		// Instancier la commande APRÈS avoir créé le JS pour que le scan fonctionne.
		$command = $this->make_command();
		$found = $command->expose_find_js_files( $this->plugin_dir );
		if (empty($found)) {
			$this->fail('Aucun fichier JS trouvé dans ' . $this->plugin_dir . ". Contenu: " . print_r(scandir($this->plugin_dir), true));
		}
		$this->assertContains($js_dir . '/app.js', $found, 'Le fichier JS attendu n’a pas été trouvé.');

		// Diagnostic : afficher le contenu du fichier JS
		$js_content = file_get_contents($js_dir . '/app.js');
		if ($js_content === false) {
			$this->fail('Impossible de lire le fichier JS pour analyse.');
		} else {
			fwrite(STDERR, "Contenu JS lu : [" . $js_content . "]\n");
		}

		$this->assertTrue(
			$command->expose_has_javascript_strings(),
			'Should detect wp.i18n usage in JS file.'
		);
	}

	public function test_has_javascript_strings_false_without_js() {
		$command = $this->make_command();
		$this->assertFalse( $command->expose_has_javascript_strings() );
	}

	public function test_find_js_files_returns_nested_files() {
		$nested_dir = $this->plugin_dir . '/sub/dir';
		mkdir( $nested_dir, 0755, true );
		file_put_contents( $nested_dir . '/one.js', '// js 1' );
		file_put_contents( $this->plugin_dir . '/root.js', '// js 2' );
		file_put_contents( $nested_dir . '/ignore.txt', 'no js' );

		$command = $this->make_command();
		$files   = $command->expose_find_js_files( $this->plugin_dir );

		$expected = array( $this->plugin_dir . '/root.js', $nested_dir . '/one.js' );
		sort( $files );
		sort( $expected );

		$this->assertEquals( $expected, $files );
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
