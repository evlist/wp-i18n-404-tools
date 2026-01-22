#!/bin/bash

# SPDX-FileCopyrightText: 2026 Eric van der Vlist <vdv@dyomedea.com>
#
# SPDX-License-Identifier: GPL-3.0-or-later

# Generate expected .pot files using official wp-cli for reference comparison.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
FIXTURES_DIR="${SCRIPT_DIR}/fixtures"
EXPECTED_DIR="${SCRIPT_DIR}/expected"

# Create expected directory.
mkdir -p "${EXPECTED_DIR}"

echo "Generating expected .pot files with wp-cli..."

# Check if wp-cli is available.
if ! command -v wp &> /dev/null; then
    echo "Error: wp-cli not found. Please install it first."
    exit 1
fi

# Generate for each fixture directory.
for fixture_dir in "${FIXTURES_DIR}"/*; do
    if [ -d "${fixture_dir}" ]; then
        fixture_name=$(basename "${fixture_dir}")
        output_file="${EXPECTED_DIR}/${fixture_name}.pot"
        
        echo "Processing ${fixture_name}..."
        wp i18n make-pot "${fixture_dir}" "${output_file}" \
            --domain=testdomain \
            --skip-audit \
            2>&1 | grep -v "^Debug:" || true
        
        if [ -f "${output_file}" ]; then
            echo "  ✓ Generated ${output_file}"
        else
            echo "  ✗ Failed to generate ${output_file}"
        fi
    fi
done

echo ""
echo "Done! Expected .pot files are in ${EXPECTED_DIR}/"

# Generate expected JSON files.
echo ""
echo "Generating expected .json files with wp-cli..."

JSON_INPUT_DIR="${FIXTURES_DIR}/json-input"

if [ -d "${JSON_INPUT_DIR}" ]; then
    for po_file in "${JSON_INPUT_DIR}"/*.po; do
        if [ -f "${po_file}" ]; then
            po_name=$(basename "${po_file}" .po)
            
            echo "Processing ${po_name}..."
            
            # Create temporary directory for JSON output.
            temp_json_dir=$(mktemp -d)
            
            # Copy .po file to temp directory.
            cp "${po_file}" "${temp_json_dir}/"
            
            # Generate JSON with wp-cli.
            wp i18n make-json "${temp_json_dir}" \
                --no-purge \
                2>&1 | grep -v "^Debug:" || true
            
            # Find generated JSON files and copy to expected directory.
            json_count=0
            for json_file in "${temp_json_dir}"/*.json; do
                if [ -f "${json_file}" ]; then
                    json_basename=$(basename "${json_file}")
                    cp "${json_file}" "${EXPECTED_DIR}/${json_basename}"
                    echo "  ✓ Generated ${EXPECTED_DIR}/${json_basename}"
                    json_count=$((json_count + 1))
                fi
            done
            
            if [ $json_count -eq 0 ]; then
                echo "  ✗ No JSON files generated for ${po_name}"
            fi
            
            # Cleanup temp directory.
            rm -rf "${temp_json_dir}"
        fi
    done
fi

echo ""
echo "Done! Expected files are in ${EXPECTED_DIR}/"
