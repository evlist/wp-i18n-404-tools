<?php
/**
 * Tests for JSON file generation.
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
 * Test JSON generation against wp-cli reference output.
 */
class JsonGenerationTest extends TestCase {
	/**
	 * Extractor instance.
	 *
	 * @var I18N_404_Extractor
	 */
	private $extractor;

	/**
	 * JSON input fixtures directory.
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
		$this->fixtures_dir = __DIR__ . '/fixtures/json-input';
		$this->expected_dir = __DIR__ . '/expected';
		$this->output_dir   = __DIR__ . '/output/json';

		// Ensure output directory exists.
		if ( ! is_dir( $this->output_dir ) ) {
			mkdir( $this->output_dir, 0755, true );
		}
	}

	/**
	 * Test simple string JSON generation.
	 */
	public function test_json_simple_strings(): void {
		$this->run_json_test( 'simple-fr_FR' );
	}

	/**
	 * Test plural forms JSON generation.
	 */
	public function test_json_plural_forms(): void {
		$this->run_json_test( 'plural-fr_FR' );
	}

	/**
	 * Test context strings JSON generation.
	 */
	public function test_json_context_strings(): void {
		$this->run_json_test( 'context-fr_FR' );
	}

	/**
	 * Test mixed strings JSON generation.
	 */
	public function test_json_mixed_strings(): void {
		$this->run_json_test( 'mixed-fr_FR' );
	}

	/**
	 * Test that only JS strings are extracted, not PHP.
	 */
	public function test_json_only_js_strings(): void {
		$fixture_name = 'php-and-js-fr_FR';
		$po_file      = $this->fixtures_dir . '/' . $fixture_name . '.po';
		$this->assertFileExists( $po_file, "Fixture .po file not found: {$po_file}" );

		// Copy .po file to output directory for processing.
		$output_po = $this->output_dir . '/' . $fixture_name . '.po';
		copy( $po_file, $output_po );

		// Generate JSON with our extractor.
		$result = $this->extractor->generate_json( $this->output_dir, array( 'purge' => false ) );
		$this->assertTrue( $result['success'], "JSON generation failed: " . ( $result['error'] ?? 'Unknown error' ) );

		// Find generated JSON file(s).
		$json_files = glob( $this->output_dir . '/' . $fixture_name . '-*.json' );
		$this->assertNotEmpty( $json_files, "No JSON files generated for fixture: {$fixture_name}" );

		// wp-cli generates one JSON file per JS source file.
		// We expect 5 JSON files (one for each .js file in the fixture).
		$this->assertCount(
			5,
			$json_files,
			"Expected 5 JSON files (one per JS source file). Fixture has: admin/script.js, assets/main.js, assets/utils.js, admin/app.js, src/components/modal.js"
		);

		// Count total entries across all JSON files (excluding metadata).
		$total_entries = 0;

		foreach ( $json_files as $json_file ) {
			$json_content = file_get_contents( $json_file );
			$json_data    = json_decode( $json_content, true );

			$this->assertIsArray( $json_data, "JSON is not valid in {$json_file}" );
			$this->assertArrayHasKey( 'locale_data', $json_data );

			$domain       = $json_data['domain'] ?? 'messages';
			$translations = $json_data['locale_data'][ $domain ] ?? array();

			// Count entries in this file (excluding metadata entry with empty string key).
			foreach ( $translations as $key => $value ) {
				if ( '' !== $key ) {
					$total_entries++;
				}
			}
		}

		// We expect exactly 5 JS entries total across all JSON files (no PHP entries).
		// Fixture has: 4 PHP strings + 5 JS strings = 9 total.
		// JSON files should only have the 5 JS strings (1 per file).
		$this->assertEquals(
			5,
			$total_entries,
			"Expected 5 JS strings total across all JSON files, found {$total_entries}. JSON files should only contain JS strings, not PHP."
		);
	}

	/**
	 * Run a JSON generation test for a fixture.
	 *
	 * @param string $fixture_name Base name of the .po fixture file (without extension).
	 */
	private function run_json_test( string $fixture_name ): void {
		$po_file = $this->fixtures_dir . '/' . $fixture_name . '.po';
		$this->assertFileExists( $po_file, "Fixture .po file not found: {$po_file}" );

		// Copy .po file to output directory for processing.
		$output_po = $this->output_dir . '/' . $fixture_name . '.po';
		copy( $po_file, $output_po );

		// Generate JSON with our extractor.
		$result = $this->extractor->generate_json( $this->output_dir, array( 'purge' => false ) );

		$this->assertTrue( $result['success'], "JSON generation failed: " . ( $result['error'] ?? 'Unknown error' ) );

		// Find generated JSON file(s).
		$json_files = glob( $this->output_dir . '/' . $fixture_name . '-*.json' );
		$this->assertNotEmpty( $json_files, "No JSON files generated for fixture: {$fixture_name}" );

		// Compare each generated JSON with expected.
		foreach ( $json_files as $actual_json ) {
			$json_basename = basename( $actual_json );
			$expected_json = $this->expected_dir . '/' . $json_basename;

			if ( file_exists( $expected_json ) ) {
				$this->compare_json_files( $expected_json, $actual_json, $fixture_name );
			} else {
				$this->markTestIncomplete( "Expected JSON file not found: {$expected_json}. Run tests/generate-expected.sh first." );
			}
		}
	}

	/**
	 * Compare two JSON files.
	 *
	 * @param string $expected_file Expected JSON file path.
	 * @param string $actual_file   Actual JSON file path.
	 * @param string $fixture_name  Name of fixture for error messages.
	 */
	private function compare_json_files( string $expected_file, string $actual_file, string $fixture_name ): void {
		$expected_content = file_get_contents( $expected_file );
		$actual_content   = file_get_contents( $actual_file );

		$expected = json_decode( $expected_content, true );
		$actual   = json_decode( $actual_content, true );

		$this->assertIsArray( $expected, "Expected JSON is not valid in {$expected_file}" );
		$this->assertIsArray( $actual, "Actual JSON is not valid in {$actual_file}" );

		// Normalize generator field (may differ).
		if ( isset( $expected['generator'] ) ) {
			$expected['generator'] = 'NORMALIZED';
		}
		if ( isset( $actual['generator'] ) ) {
			$actual['generator'] = 'NORMALIZED';
		}

		// Normalize translation-revision-date (may differ).
		if ( isset( $expected['translation-revision-date'] ) ) {
			$expected['translation-revision-date'] = 'NORMALIZED';
		}
		if ( isset( $actual['translation-revision-date'] ) ) {
			$actual['translation-revision-date'] = 'NORMALIZED';
		}

		$this->assertEquals(
			$expected,
			$actual,
			"JSON content mismatch for fixture: {$fixture_name}"
		);
	}

	/**
	 * Cleanup after each test.
	 */
	protected function tearDown(): void {
		// Clean up generated .po and .json files in output directory.
		$files_to_clean = array_merge(
			glob( $this->output_dir . '/*.po' ),
			glob( $this->output_dir . '/*.json' )
		);

		foreach ( $files_to_clean as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
	}
}
