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
