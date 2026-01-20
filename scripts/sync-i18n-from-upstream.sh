#!/bin/bash
# SPDX-FileCopyrightText: 2025 Eric van der Vlist <vdv@dyomedea.com>
#
# SPDX-License-Identifier: GPL-3.0-or-later
#
# Syncs wp-cli/i18n-command code into i18n-404-tools/admin/wp-cli/src/
# Usage: bash scripts/sync-i18n-from-upstream.sh
#

set -e

MODE="sync"
FORCE=false
YEAR=$(date +%Y)
YEAR_RANGE="2011-$YEAR"

for arg in "$@"; do
    case "$arg" in
        --check)
            MODE="check"
            ;;
        --force)
            FORCE=true
            ;;
        *)
            echo "Unknown option: $arg" >&2
            exit 1
            ;;
    esac
done

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Paths
UPSTREAM_REPO="https://github.com/wp-cli/i18n-command.git"
TARGET_DIR="i18n-404-tools/admin/wp-cli"
SRC_DIR="$TARGET_DIR/src"
SYNC_LOG="$TARGET_DIR/SYNC-LOG.md"
VENDOR_INFO="$TARGET_DIR/vendor-info.json"
TEMP_DIR="/tmp/wp-cli-i18n-sync-$$"
CACHED_COMMIT=""

# Messages
echo -e "${YELLOW}🔄 Syncing wp-cli/i18n-command...${NC}"
echo "Upstream repo: $UPSTREAM_REPO"
echo "Target: $SRC_DIR"
echo ""

# Create working directory
mkdir -p "$TEMP_DIR"
trap "rm -rf $TEMP_DIR" EXIT

# Clone upstream repository
echo -e "${YELLOW}📥 Cloning upstream repository...${NC}"
git clone --depth=1 "$UPSTREAM_REPO" "$TEMP_DIR/i18n-command" 2>/dev/null || {
    echo -e "${RED}❌ Error: failed to clone repository${NC}"
    exit 1
}

# Get commit information
cd "$TEMP_DIR/i18n-command"
COMMIT=$(git rev-parse HEAD | cut -c1-7)
COMMIT_FULL=$(git rev-parse HEAD)
COMMIT_DATE=$(git log -1 --format=%ai)
COMMIT_MSG=$(git log -1 --format=%B | head -1)
cd - > /dev/null

echo -e "${GREEN}✓ Clone successful${NC}"
echo "  Commit: $COMMIT"
echo "  Date: $COMMIT_DATE"
echo "  Message: $COMMIT_MSG"

if [ -f "$VENDOR_INFO" ]; then
    CACHED_COMMIT=$(python3 - "$VENDOR_INFO" <<'PY'
import json, sys
try:
    with open(sys.argv[1]) as f:
        data = json.load(f)
    print(data.get("commit", ""))
except Exception:
    pass
PY
)
fi

if [ -n "$CACHED_COMMIT" ]; then
    echo "  Current installed commit: ${CACHED_COMMIT:0:7}"
else
    echo "  Current installed commit: none"
fi
echo ""

if [ "$MODE" = "check" ]; then
    if [ "$CACHED_COMMIT" = "$COMMIT_FULL" ]; then
        echo -e "${GREEN}Up-to-date. No sync needed.${NC}"
        exit 0
    else
        CURRENT_SHORT=${CACHED_COMMIT:0:7}
        [ -z "$CURRENT_SHORT" ] && CURRENT_SHORT="none"
        echo -e "${YELLOW}Update available. Upstream: $COMMIT (current: $CURRENT_SHORT)${NC}"
        exit 2
    fi
fi

if [ "$CACHED_COMMIT" = "$COMMIT_FULL" ] && [ "$FORCE" = false ]; then
    echo -e "${GREEN}Already at latest commit ($COMMIT). Skipping sync. Use --force to re-sync.${NC}"
    exit 0
fi
echo "Proceeding with sync..."

# Clean target directory
if [ -d "$SRC_DIR" ]; then
    echo -e "${YELLOW}🧹 Cleaning $SRC_DIR...${NC}"
    rm -rf "$SRC_DIR"
fi

# Copy source files
echo -e "${YELLOW}📋 Copying source files...${NC}"
mkdir -p "$SRC_DIR"
cp -r "$TEMP_DIR/i18n-command/src"/* "$SRC_DIR/"

echo -e "${GREEN}✓ Copy successful${NC}"
ls -1 "$SRC_DIR" | head -5
echo "  ... and $(ls -1 "$SRC_DIR" | wc -l) file(s) total"
echo ""

# Add SPDX license headers to all PHP files
echo -e "${YELLOW}⚖️  Adding SPDX license headers...${NC}"
if command -v reuse >/dev/null 2>&1; then
    reuse annotate \
        --copyright "WP-CLI Contributors <https://wp-cli.org>" \
        --license "MIT" \
        --year "$YEAR_RANGE" \
        --recursive \
        "$SRC_DIR" >/dev/null 2>&1 && {
            echo -e "${GREEN}✓ SPDX headers added to all vendored files${NC}"
        } || {
            echo -e "${YELLOW}  ⚠️  Some headers may already exist or failed to add${NC}"
        }
else
    echo -e "${YELLOW}  ⚠️  Warning: 'reuse' tool not found, skipping SPDX headers${NC}"
    echo -e "${YELLOW}  ℹ️  Install with: pip install reuse${NC}"
fi
echo ""

# Copy composer files
echo -e "${YELLOW}📦 Copying dependency information...${NC}"
cp "$TEMP_DIR/i18n-command/composer.json" "$TARGET_DIR/composer.json.upstream" 2>/dev/null || true

# Create vendor-info.json file
echo -e "${YELLOW}📝 Updating metadata...${NC}"
cat > "$VENDOR_INFO" << EOF
{
  "upstream": "https://github.com/wp-cli/i18n-command",
  "last_sync": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "commit": "$COMMIT_FULL",
  "commit_short": "$COMMIT",
  "commit_date": "$COMMIT_DATE",
  "commit_message": "$COMMIT_MSG",
  "notes": "Code copied exactly from wp-cli/i18n-command without modifications"
}
EOF

echo -e "${GREEN}✓ vendor-info.json created${NC}"

# Update SYNC-LOG
if [ ! -f "$SYNC_LOG" ]; then
    cat > "$SYNC_LOG" << 'EOF'
# Synchronization history with wp-cli/i18n-command

## Format
- Sync date
- Commit
- Notes about changes

## Syncs

EOF
fi

# Add new entry to log
cat >> "$SYNC_LOG" << EOF

### $(date '+%Y-%m-%d %H:%M:%S')
- **Commit**: $COMMIT ($COMMIT_FULL)
- **Commit date**: $COMMIT_DATE
- **Message**: $COMMIT_MSG
- **Files copied**: $(ls -1 "$SRC_DIR" | wc -l)
- **Status**: ✅ Synced successfully

EOF

echo -e "${GREEN}✓ SYNC-LOG.md updated${NC}"

# Final summary
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Synchronization completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "📊 Summary:"
echo "  - Commit: $COMMIT"
echo "  - Files: $(ls -1 "$SRC_DIR" | wc -l)"
echo "  - Target: $SRC_DIR"
echo "  - Metadata: $VENDOR_INFO"
echo "  - Log: $SYNC_LOG"
echo ""
echo -e "${YELLOW}⚠️  Next steps:${NC}"
echo "  1. Check copied files: ls -la $SRC_DIR"
echo "  2. Test integration"
echo "  3. Commit changes: git add $TARGET_DIR/"
echo ""
