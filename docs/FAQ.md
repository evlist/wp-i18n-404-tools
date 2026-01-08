<!--
SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<img src="../i18n-404-tools/admin/images/logo.svg" width="96" style="float:right;max-width:96px;height:auto" alt="WP i18n-404-tools Logo" /> 

# ❓ FAQ — wp-i18n-404-tools

- **Why does “Generate JSON” say “Created 0 files”?**
  Your JS doesn’t use `wp.i18n` translation functions. The plugin detects this and skips JSON generation.

- **Do I still need Loco Translate?**
  Yes, [Loco Translate](https://wordpress.org/plugins/loco-translate/) (or Poedit) is recommended to edit `.po`/`.mo`. This plugin handles the CLI-only steps (.pot / JSON generation) directly from wp-admin.

- **How do I rebuild `.mo` after editing `.po`?**
  From Loco (auto) or via CLI: `wp i18n make-mo i18n-404-tools/languages/`.

- **Permission denied writing `.po`/`.mo`?**
  Ensure language files are writable by both the editor user and `www-data` (e.g., `chown -R vscode:www-data i18n-404-tools/languages && chmod 775 languages && chmod 664 languages/*`).

- **Why this plugin instead of just WP-CLI or Loco?**
  It wraps the JS translation refresh into a one-click admin flow, so non-CLI users keep JSON bundles in sync without shell access.

- **How do I add a new command?**
  Create a class extending the base command, register it in the AJAX router, and expose it via the plugin action links to open the modal (see docs/DEVELOPERS.md).
