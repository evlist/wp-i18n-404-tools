#!/usr/bin/env bash
set -euo pipefail

# Build installable plugin ZIP containing only the plugin folder
# Output: dist/i18n-404-tools.zip

ROOT_DIR="$(cd "$(dirname "$0")"/.. && pwd)"
PLUGIN_DIR="${ROOT_DIR}/i18n-404-tools"
DIST_DIR="${ROOT_DIR}/dist"
ZIP_PATH="${DIST_DIR}/i18n-404-tools.zip"

if [[ ! -d "${PLUGIN_DIR}" ]]; then
  echo "Plugin directory not found: ${PLUGIN_DIR}" >&2
  exit 1
fi

mkdir -p "${DIST_DIR}"

# Create ZIP with a single top-level folder: i18n-404-tools/
cd "${ROOT_DIR}"

# Exclude common junk and VCS
zip -r "${ZIP_PATH}" i18n-404-tools \
  -x "i18n-404-tools/**/.DS_Store" \
  -x "i18n-404-tools/**/.git*" \
  -x "i18n-404-tools/**/node_modules/*"

echo "Built: ${ZIP_PATH}"
