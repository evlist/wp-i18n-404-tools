<!--
SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>

SPDX-License-Identifier: GPL-3.0-or-later
-->

<img src=".devcontainer/assets/icon.svg" width="64" height="64" alt="cs-grafting" />Codespace created with [evlist/codespaces-grafting](https://github.com/evlist/codespaces-grafting) -
[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://github.com/codespaces/new?hide_repo_select=true&ref=graft/2026-01-05T23-58-42&repo=evlist/wp-i18n-404-tools)
[![CI](https://github.com/evlist/wp-i18n-404-tools/actions/workflows/ci.yml/badge.svg)](https://github.com/evlist/wp-i18n-404-tools/actions/workflows/ci.yml)
---

# WP i18n-404-tools

  <img src="./i18n-404-tools/admin/images/logo.svg" width="256" style="float:right;max-width:256px;height:auto" alt="WP i18n-404-tools Logo" /> A WordPress plugin with missing i18N (internationalization) tools.

## ✨ What this plugin does

This plugin provides one-click i18n maintenance (generate `.pot`, regenerate JSON, copy command output) directly from the WordPress admin, without leaving the browser or running the CLI. It complements tools like **Loco Translate** (recommended for editing `.po`/`.mo`) by handling the operations that typically require WP‑CLI.

### ⚠️ Technical Note: WP-CLI Execution

This plugin needs to run **WP-CLI** to perform i18n operations, but you do not need to install WP-CLI yourself. The plugin automatically downloads and manages a local `wp-cli.phar` in WordPress uploads, protected by `.htaccess`.

Some hosting providers restrict or disable the PHP function `shell_exec()`. This function must be enabled for the plugin to execute the local `wp-cli.phar`. If `shell_exec()` is blocked on your server, please ask your hosting provider to enable it or use a host that supports WP-CLI execution.

## 📚 Developer Docs

See [docs/DEVELOPERS.md](docs/DEVELOPERS.md) for architecture and the i18n workflow.

## ❓ FAQ

See [docs/FAQ.md](docs/FAQ.md).


## ⬇️ Downloads

- Nightly ZIP: available on the repository Releases page under the pre‑release tagged `nightly`. It is updated automatically on every push.
- Workflow artifact: from the latest Actions run, download the `plugin-zip` artifact (transient; expires after ~90 days).

