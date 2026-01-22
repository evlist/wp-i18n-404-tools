<?php
/**
 * Tests for POT file generation.
 *
 * @package I18n_404_Tools_Tests
 */

/*
 * SPDX-FileCopyrightText: 2026 Eric van der Vlist <vdv@dyomedea.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

use PHPUnit\Framework\TestCase;

/**
 * Test POT generation against wp-cli reference output.
 */
class PotGenerationTest extends TestCase {
	/**
	 * Extractor instance.
	 *
	 * @var I18N_404_Extractor
	 */
	private $extractor;

	/**
	 * Fixtures directory.
	 *
	 * @var string
	 */
	private $fixtures_dir;

	/**
	 * Expected output directory.
	 *
	 * @var string
	 */
	private $expected_dir;

	/**
	 * Test output directory.
	 *
	 * @var string
	 */
	private $output_dir;

	/**
	 * Setup before each test.
	 */
	protected function setUp(): void {
		$this->extractor    = new I18N_404_Extractor();
		$this->fixtures_dir = __DIR__ . '/fixtures';
		$this->expected_dir = __DIR__ . '/expected';
		$this->output_dir   = __DIR__ . '/output';

		// Ensure output directory exists.
		if ( ! is_dir( $this->output_dir ) ) {
			mkdir( $this->output_dir, 0755, true );
		}
	}

	/**
	 * Test simple PHP string extraction.
	 */
	public function test_php_simple_strings(): void {
		$this->run_pot_test( 'php-simple' );
	}

	/**
	 * Test PHP plural forms.
	 */
	public function test_php_plural_forms(): void {
		$this->run_pot_test( 'php-plural' );
	}

	/**
	 * Test PHP context strings.
	 */
	public function test_php_context_strings(): void {
		$this->run_pot_test( 'php-context' );
	}

	/**
	 * Test PHP translator comments.
	 */
	public function test_php_translator_comments(): void {
		$this->run_pot_test( 'php-comments' );
	}

	/**
	 * Test PHP heredoc and nowdoc syntax.
	 */
	public function test_php_heredoc_nowdoc(): void {
		$this->run_pot_test( 'php-heredoc' );
	}

	/**
	 * Test PHP escape sequences.
	 */
	public function test_php_escape_sequences(): void {
		$this->run_pot_test( 'php-escapes' );
	}

	/**
	 * Test simple JavaScript string extraction.
	 */
	public function test_js_simple_strings(): void {
		$this->run_pot_test( 'js-simple' );
	}

	/**
	 * Test JavaScript plural forms.
	 */
	public function test_js_plural_forms(): void {
		$this->run_pot_test( 'js-plural' );
	}

	/**
	 * Test JavaScript template literals.
	 */
	public function test_js_template_literals(): void {
		$this->run_pot_test( 'js-template-literals' );
	}

	/**
	 * Test JavaScript escape sequences.
	 */
	public function test_js_escape_sequences(): void {
		$this->run_pot_test( 'js-escapes' );
	}

	/**
	 * Test JavaScript dynamic strings (should not extract).
	 */
	public function test_js_dynamic_strings(): void {
		$this->run_pot_test( 'js-dynamic' );
	}

	/**
	 * Run a POT generation test for a fixture.
	 *
	 * @param string $fixture_name Name of the fixture directory.
	 */
	private function run_pot_test( string $fixture_name ): void {
		$source      = $this->fixtures_dir . '/' . $fixture_name;
		$output      = $this->output_dir . '/' . $fixture_name . '.pot';
		$expected    = $this->expected_dir . '/' . $fixture_name . '.pot';

		$this->assertDirectoryExists( $source, "Fixture directory not found: {$source}" );

		// Generate POT with our extractor.
		$result = $this->extractor->generate_pot( $source, $output, 'testdomain' );

		$this->assertTrue( $result['success'], "POT generation failed: " . ( $result['error'] ?? 'Unknown error' ) );
		$this->assertFileExists( $output, "Output POT file not created: {$output}" );

		// If expected file exists, compare outputs.
		if ( file_exists( $expected ) ) {
			$this->compare_pot_files( $expected, $output, $fixture_name );
		} else {
			$this->markTestIncomplete( "Expected file not found: {$expected}. Run tests/generate-expected.sh first." );
		}
	}

	/**
	 * Compare two POT files, normalizing dates and paths.
	 *
	 * @param string $expected_file Expected POT file path.
	 * @param string $actual_file   Actual POT file path.
	 * @param string $fixture_name  Name of fixture for error messages.
	 */
	private function compare_pot_files( string $expected_file, string $actual_file, string $fixture_name ): void {
		$expected = $this->normalize_pot_content( file_get_contents( $expected_file ) );
		$actual   = $this->normalize_pot_content( file_get_contents( $actual_file ) );

		// Extract entries (ignoring headers which may vary).
		$expected_entries = $this->extract_pot_entries( $expected );
		$actual_entries   = $this->extract_pot_entries( $actual );

		$this->assertEquals(
			$expected_entries,
			$actual_entries,
			"POT entries mismatch for fixture: {$fixture_name}"
		);
	}

	/**
	 * Normalize POT file content for comparison.
	 *
	 * @param string $content POT file content.
	 * @return string Normalized content.
	 */
	private function normalize_pot_content( string $content ): string {
		// Remove variable dates.
		$content = preg_replace( '/^"POT-Creation-Date:.*$/m', '"POT-Creation-Date: NORMALIZED"', $content );
		$content = preg_replace( '/^"PO-Revision-Date:.*$/m', '"PO-Revision-Date: NORMALIZED"', $content );

		// Normalize paths.
		$content = preg_replace( '/^#: .*\/(fixtures\/.*)$/m', '#: $1', $content );

		// Normalize generator.
		$content = preg_replace( '/^"X-Generator:.*$/m', '"X-Generator: NORMALIZED"', $content );

		return $content;
	}

	/**
	 * Extract msgid/msgstr entries from POT content.
	 *
	 * @param string $content POT file content.
	 * @return array Array of entries.
	 */
	private function extract_pot_entries( string $content ): array {
		$entries = array();
		$lines   = explode( "\n", $content );
		$current = array();

		foreach ( $lines as $line ) {
			$line = trim( $line );

			if ( empty( $line ) ) {
				if ( ! empty( $current ) ) {
					$entries[] = $current;
					$current   = array();
				}
				continue;
			}

			if ( strpos( $line, '#:' ) === 0 || strpos( $line, '#.' ) === 0 || strpos( $line, 'msgid' ) === 0 || strpos( $line, 'msgstr' ) === 0 || strpos( $line, 'msgid_plural' ) === 0 || strpos( $line, 'msgctxt' ) === 0 ) {
				$current[] = $line;
			}
		}

		if ( ! empty( $current ) ) {
			$entries[] = $current;
		}

		return $entries;
	}
}
