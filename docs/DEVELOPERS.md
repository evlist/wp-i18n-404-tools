<!--
SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<img src="../i18n-404-tools/admin/images/logo.svg" width="96" style="float:right;max-width:96px;height:auto" alt="WP i18n-404-tools Logo" />

# 🛠️ Developer Documentation — wp-i18n-404-tools

This guide explains how the plugin is structured, how to develop and test locally, and the i18n workflow (.pot/.po/.mo and JSON generation).

---

## 📌 Overview

- Purpose: Provide missing internationalization tools for WordPress plugins.
- Key features:
  - Generate `.pot` via WP‑CLI without leaving wp-admin
  - Generate `.json` for JS translations when needed
  - Admin UI with modal actions and AJAX router

## 💻 Recommended dev environment (Codespaces)

Open this repository directly in GitHub Codespaces using the prebuilt devcontainer:

[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://github.com/codespaces/new?hide_repo_select=true&ref=graft/2026-01-05T23-58-42&repo=evlist/wp-i18n-404-tools)

Notes:
- The Codespace was “grafted” using [evlist/codespaces-grafting](https://github.com/evlist/codespaces-grafting).
- Ubuntu 24.04 devcontainer with PHP/WP tooling pre-installed.
- No additional bootstrap script is required; just open the Codespace and run your usual WordPress stack.

---

## 🗂️ Repository Structure

```
i18n-404-tools/
  i18n-404-tools.php           # Main plugin bootstrap
  admin/
    class-generate-pot-command.php
    class-generate-json-command.php
    class-i18n-ajax-router.php
    class-i18n-command-base.php
    class-wpcli-updater.php
    helpers.php, modal-config.php
    js/i18n-404-tools-modal.js
    css/i18n-404-tools-admin.css
  languages/
    i18n-404-tools.pot
    i18n-404-tools-<locale>.po / .mo / .json
scripts/
  bootstrap-wp.sh              # Dev helper for local WP
```

---

## 🧭 Local dev notes

- The devcontainer already ships with PHP, WP‑CLI, and common tools.
- If you run locally (outside Codespaces), install WP‑CLI and ensure your WordPress instance is reachable for testing the plugin.

---

## 🧱 Plugin Architecture

### 🔌 Bootstrap: `i18n-404-tools/i18n-404-tools.php`
- Loads textdomain and wires admin UI actions.
- Localizes modal strings for JS via `wp_localize_script()`.

### 🔀 AJAX Router: `admin/class-i18n-ajax-router.php`
- Receives `action=i18n_404_tools_command` requests.
- Dynamically loads command classes and invokes `run_step()`.

### 🧰 Command Base: `admin/class-i18n-command-base.php`
- Resolves plugin paths (`plugin_dir`, `languages_dir`, `pot_path`).
- Provides helpers to run WP‑CLI subcommands using the bundled PHAR.
- Generates modal buttons and handles common UI pieces.

### ⬇️ WP‑CLI Updater: `admin/class-wpcli-updater.php`
- Ensures a working `wp-cli.phar` is available for commands.

### 🛎️ Admin Commands
- `class-generate-pot-command.php`: Runs `wp i18n make-pot` and shows output.
- `class-generate-json-command.php`: Checks/creates JS translation JSON when needed.

### 🧩 Adding a new command
1. Create a class in `admin/` extending `I18N_404_Command_Base` and implement `run_step( $step, $request )`.
2. Map the command in `admin/class-i18n-ajax-router.php` (`$commands` array: `command_slug => [ ClassName, file-name.php ]`).
3. Expose the action in the admin list (e.g., update the `plugin_action_links` filter in `i18n-404-tools.php` using `i18n404tools_action_attrs()` to open the modal).
4. Provide UI text in `modal-config.php`/translations as needed.

---

## 🌐 i18n Workflow

### Files
- `.pot` — Template of all source strings (no translations).
- `.po` — Human‑editable translations.
- `.mo` — Compiled binary translations loaded by WordPress.
- `.json` — Only needed if JavaScript uses `wp.i18n` translation functions.

### Generate POT
Triggered from the plugin UI (Generate .pot), which runs:
```php
// admin/class-generate-pot-command.php
$this->run_wp_cli_command(
  'i18n make-pot',
  [
    0        => $this->plugin_dir,
    1        => $this->pot_path,
    'domain' => $this->domain,
  ]
);
```

### Update PO/MO
Edit `.po` (e.g., Poedit/[Loco Translate](https://wordpress.org/plugins/loco-translate/)). Regenerate `.mo`:
```bash
wp i18n make-mo ./i18n-404-tools/languages/
# Or with gettext:
msgfmt -o i18n-404-tools-fr_FR.mo i18n-404-tools-fr_FR.po
```

### Generate JSON (JS translations)
The plugin detects whether JS translations are needed:
- `admin/class-generate-json-command.php` has `has_javascript_strings()` which scans `.js` files for `wp.i18n.__`, `_x`, etc.
- If none are found, the UI displays: “JSON files not needed”.
- If found, the plugin runs:
```php
run_wp_cli_command('i18n make-json', [
  0          => $this->languages_dir,
  'no-purge' => null, // avoids rewriting .po
]);
```
Notes:
- `--no-purge` is used intentionally to prevent WP‑CLI from modifying `.po` files.

### 🧭 Workflow at a glance

| Phase | Responsible tool | How | Class / UI |
| --- | --- | --- | --- |
| Generate POT | Plugin UI | “Generate .pot” action (runs `wp i18n make-pot`) | `admin/class-generate-pot-command.php` |
| Update `.po` | Loco Translate (recommended) or translators | Edit translations in Loco/Poedit | n/a (external) |
| Generate `.mo` | Loco Translate (auto) or CLI | `wp i18n make-mo` or `msgfmt` | n/a (external) |
| Generate JSON (if JS uses `wp.i18n`) | Plugin UI | “Generate JSON” (runs `wp i18n make-json --no-purge`) | `admin/class-generate-json-command.php` |

### 📝 JavaScript localization options

1) **PHP → JS localization (used by this plugin)**: strings are translated in PHP (`__()`), then passed to JS via `wp_localize_script()` (see `i18n-404-tools.php`). No JSON files needed.
2) **JS-native `wp.i18n`**: strings are translated directly in JS using `wp.i18n.__`, `_x`, etc. In this case, JSON files are required and the “Generate JSON” action will create them.

---

## 🔐 Permissions (dev container)
To allow both the editor (`vscode`) and WordPress (`www-data`) to write language files:
```bash
sudo chown -R vscode:www-data ./i18n-404-tools/languages/
sudo chmod 775 ./i18n-404-tools/languages/
sudo chmod 664 ./i18n-404-tools/languages/*.{po,pot,mo,php,json}
```
Result:
- Owner: `vscode` (developer)
- Group: `www-data` (WordPress/WP‑CLI)
- Files: `rw-rw-r--` (664), directory: `rwxrwxr-x` (775)

---

## 🪟 Admin UI Flow
- Plugin list row includes “Generate .pot” and “Generate JSON”.
- Clicking opens a modal (HTML via command classes and `modal-config.php`).
- Output is captured and presented with a copy‑to‑clipboard button.

---

## 🧪 Testing & Troubleshooting

### Verify POT completeness
Use `wp i18n make-pot` and compare `.pot` with code.
(Optionally build a script to diff `msgid` entries vs. extracted strings.)

### Common issues
- “Created 0 files” for JSON: This is expected if no `wp.i18n` usage in JS.
- Permission denied: Fix ownership/permissions as above.
- Missing translations: Ensure text domain matches (`i18n-404-tools`) in PHP.

---

## 🚀 Extending: AI Translation Providers (optional)
It should be possible to integrate AI APIs (OpenAI, Anthropic, Google Gemini, Mistral) by adding a settings page and a translation service class. Allow users to supply their own API keys and choose a provider; keep keys out of source control. See [issue #12](https://github.com/evlist/wp-i18n-404-tools/issues/12) for discussion and future directions.

---

## 📚 Resources

- README with purpose and quick links
- FAQ: [docs/FAQ.md](FAQ.md)
- Logo reference: [docs/logo.md](logo.md)

---

## Coding Guidelines
- Keep changes minimal and focused.
- Follow WordPress coding standards for PHP/JS/CSS.
- Use `__()`, `_e()`, `_x()`, `_n()` correctly with the `i18n-404-tools` domain.

---

## Contributing
- Open issues and PRs on the repository.
- Describe changes clearly and include steps to verify.
- Mind i18n consistency and permissions in the dev container.

---

## 🚀 Repository structure: GitHub vs WordPress.org

### GitHub repository layout
This repository serves **developers** working on the plugin itself. It includes documentation, dev tools, and the plugin source:

```
wp-i18n-404-tools/              ← GitHub repo root
├── README.md                    ← For GitHub (developers)
├── composer.json                ← Dev dependencies
├── docs/                        ← Developer docs (DEVELOPERS.md, FAQ.md, etc.)
├── scripts/                     ← Bootstrap scripts
├── assets/                      ← Plugin icons & banners (for WP.org)
│   ├── icon-128x128.png
│   ├── icon-256x256.png
│   ├── banner-772x250.png
│   └── banner-1544x500.png
└── plugin/                      ← The WordPress plugin itself
    ├── i18n-404-tools.php
    ├── README.txt               ← For WordPress.org users
    ├── uninstall.php
    ├── admin/
    ├── languages/
    └── ...
```

### WordPress.org plugin directory structure
When published, only the **plugin folder** and **assets** are submitted via SVN:

```
i18n-404-tools/                 ← WordPress.org SVN repo
├── trunk/                       ← Development version
│   ├── i18n-404-tools.php
│   ├── README.txt
│   ├── uninstall.php
│   ├── admin/
│   ├── languages/
│   └── ...
├── tags/
│   └── 1.0.0/                   ← Release tags (tagged versions)
│       └── (copy of trunk at release time)
└── assets/                      ← Plugin icons & banners
    ├── icon-128x128.png
    ├── icon-256x256.png
    ├── banner-772x250.png
    └── banner-1544x500.png
```

### What goes where

| File/Folder | GitHub | WordPress.org | Notes |
|---|---|---|---|
| `README.md` | ✅ Root | ❌ Not used | For developers on GitHub |
| `docs/` | ✅ Root | ❌ Not used | Developer documentation |
| `composer.json` | ✅ Root | ❌ Not used | Dev tools only |
| `plugin/` | ✅ Root | ✅ Becomes `/trunk/` | The actual WordPress plugin |
| `README.txt` | ✅ In plugin | ✅ In `/trunk/` | Plugin description for users |
| `assets/` | ✅ Root | ✅ `/assets/` | Icons, banners, screenshots |

### Publication workflow

1. **Develop locally** in this GitHub repository (`plugin/` folder)
2. **Tag a release** (e.g., `v1.0.0`)
3. **Submit to WordPress.org** via SVN:
   - Copy `plugin/` contents → `wp.org/trunk/`
   - Copy `assets/` → `wp.org/assets/`
   - Create a tag (e.g., `wp.org/tags/1.0.0/`)
4. **Users install** from WordPress.org directory

The GitHub repository is your **development hub**; WordPress.org is your **distribution channel**.
