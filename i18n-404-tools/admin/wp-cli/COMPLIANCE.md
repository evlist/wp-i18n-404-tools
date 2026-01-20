<!--
SPDX-FileCopyrightText: 2025 Eric van der Vlist <vdv@dyomedea.com>

SPDX-License-Identifier: GPL-3.0-or-later
-->

# WP-CLI Vendored Code: PHPCS and License Compliance

## Problem

The vendored WP-CLI i18n-command source code (in `i18n-404-tools/admin/wp-cli/src/`) generates:
- 44 PHPCS violations (naming conventions, doc comments, array syntax)
- Missing SPDX license headers

## Solution

### 1. PHPCS Exclusion

Added exclusion pattern in [`phpcs.xml`](../../../phpcs.xml):
```xml
<exclude-pattern>*/admin/wp-cli/src/*</exclude-pattern>
```

**Rationale:** 
- Vendored code should maintain upstream style
- Avoid conflicts when syncing updates
- WP-CLI uses WordPress Coding Standards but with their own configuration

### 2. License Compliance with SPDX Headers

The sync script automatically adds SPDX license headers to all vendored PHP files:
```php
<?php
/**
 * SPDX-FileCopyrightText: 2011-{YEAR} WP-CLI Contributors <https://wp-cli.org>
 *
 * SPDX-License-Identifier: MIT
 */
```

**Rationale:**
- Headers embedded directly in source files travel with the code
- Plugin distribution includes proper license information (no external `.reuse/dep5` needed)
- Properly credits upstream authors (WP-CLI Contributors)
- Uses correct license (MIT, as per upstream LICENSE file)
- Automated via `reuse annotate --recursive` in [`sync-i18n-from-upstream.sh`](../../../scripts/sync-i18n-from-upstream.sh)
- Maintains traceability via `vendor-info.json` and `SYNC-LOG.md`

**Note:** If `reuse` tool is not available, the script will warn but continue. Headers can be added manually later.

## Verification

```bash
# Add SPDX headers to existing files (one-time migration)
bash scripts/add-spdx-headers.sh

# Check PHPCS excludes vendored files
vendor/bin/phpcs i18n-404-tools/admin/wp-cli/src/
# Should show: "No files to process"

# Check license compliance
reuse lint
# Should pass without errors for wp-cli/src/* files

# Verify headers were added
head -n 10 i18n-404-tools/admin/wp-cli/src/MakePotCommand.php
# Should show SPDX headers at the top

# Dry-run check for updates
bash scripts/sync-i18n-from-upstream.sh --check
# Exit code 0 if up-to-date, 2 if an update is available
```

## Future Syncs

When running [`scripts/sync-i18n-from-upstream.sh`](../../../scripts/sync-i18n-from-upstream.sh), SPDX headers will be automatically added to all PHP files. No manual intervention needed.

## References

- Upstream repository: https://github.com/wp-cli/i18n-command
- Upstream license: MIT (see LICENSE file in repo)
- Sync script: [`scripts/sync-i18n-from-upstream.sh`](scripts/sync-i18n-from-upstream.sh)
- Version tracking: [`i18n-404-tools/admin/wp-cli/vendor-info.json`](i18n-404-tools/admin/wp-cli/vendor-info.json)
