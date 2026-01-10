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

- **Why does this plugin rely on WP‑CLI (shell execution)? Is it safe?**
  [WP‑CLI](https://wp-cli.org/) is the canonical tool for extracting strings ([`make-pot`](https://developer.wordpress.org/cli/commands/i18n/make-pot/)) and generating JS translation JSON ([`make-json`](https://developer.wordpress.org/cli/commands/i18n/make-json/)). The plugin wraps these commands to offer a one‑click admin experience. Security measures include: only admins can trigger actions; all inputs are sanitized/escaped; and AJAX requests must be nonce‑protected. On hardened hosts, ensure only trusted admins can access wp‑admin, and keep the server up to date.

- **PHP configuration requirements to run WP‑CLI from PHP?**
  The server must allow process execution. Typical requirements:
  - [`proc_open()`](https://www.php.net/proc_open) and/or [`exec()`](https://www.php.net/exec) not listed in [`disable_functions`](https://www.php.net/manual/en/ini.core.php#ini.disable-functions)
  - [`open_basedir`](https://www.php.net/manual/en/ini.core.php#ini.open-basedir) must allow the plugin path and temp directories
  - Sufficient memory/time limits for running `php wp-cli.phar`
  - Outbound HTTP allowed if the PHAR is downloaded at runtime
  If your host blocks these, run WP‑CLI via SSH/cron instead, or use Loco Translate for `.po/.mo` management and skip the JSON step if your JS doesn’t use `wp.i18n`.

- **Host compatibility: Do I need to install WP‑CLI myself?**
  No. The plugin automatically downloads and manages a local `wp-cli.phar` into `/wp-content/uploads/i18n-404-tools/`, and writes an `.htaccess` to prevent direct access to `.phar`/`.htaccess`. Your hosting must allow `shell_exec()` for the plugin to execute this local PHAR. If `shell_exec()` is disabled, contact your provider to enable it or use a host that supports WP‑CLI execution. This keeps installation hands‑free while ensuring secure, local execution.
