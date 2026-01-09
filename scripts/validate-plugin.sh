#!/bin/bash

# SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
#
# SPDX-License-Identifier: GPL-3.0-or-later

set -e

PLUGIN_DIR="i18n-404-tools"
README="$PLUGIN_DIR/README.txt"
PLUGIN_FILE="$PLUGIN_DIR/i18n-404-tools.php"
ERRORS=0

echo "Validating plugin structure..."

# Check README.txt sections
echo "Checking README.txt sections..."
REQUIRED_SECTIONS=("Description" "Installation" "Changelog" "Frequently Asked Questions")
for section in "${REQUIRED_SECTIONS[@]}"; do
    if ! grep -q "^== $section ==" "$README"; then
        echo "ERROR: Missing required section '== $section ==' in README.txt"
        ((ERRORS++))
    fi
done

# Check plugin headers
echo "Checking plugin headers in $PLUGIN_FILE..."
REQUIRED_HEADERS=("Plugin Name" "Plugin URI" "Description" "Version" "License" "License URI")
for header in "${REQUIRED_HEADERS[@]}"; do
    if ! grep -q " \* $header:" "$PLUGIN_FILE"; then
        echo "ERROR: Missing required header '$header' in plugin file"
        ((ERRORS++))
    fi
done

# Check version consistency between README and plugin file
README_VERSION=$(grep "^Stable tag:" "$README" | sed 's/Stable tag: //')
PLUGIN_VERSION=$(grep " \* Version:" "$PLUGIN_FILE" | sed "s/.*Version:[[:space:]]*//")

if [ "$README_VERSION" != "$PLUGIN_VERSION" ]; then
    echo "ERROR: Version mismatch - README.txt has '$README_VERSION' but plugin file has '$PLUGIN_VERSION'"
    ((ERRORS++))
fi

echo "Version consistency check: README=$README_VERSION, Plugin=$PLUGIN_VERSION"

if [ $ERRORS -eq 0 ]; then
    echo "✓ Plugin validation passed!"
    exit 0
else
    echo "✗ Plugin validation failed with $ERRORS error(s)"
    exit 1
fi
