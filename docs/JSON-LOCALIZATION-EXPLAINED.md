<!--
SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>

SPDX-License-Identifier: GPL-3.0-or-later
-->

# WordPress Plugin Localization Methods (2025-2026)

## 🌍 Overview of Localization Approaches

WordPress plugins can be localized using different methods, each with specific advantages and use cases.

---

## 1️⃣ **Classic Method: `.po`/`.mo` + `load_plugin_textdomain()`**

### Code Example
```php
// In main plugin file
function my_plugin_load_textdomain() {
    load_plugin_textdomain( 
        'my-plugin', 
        false, 
        dirname( plugin_basename( __FILE__ ) ) . '/languages' 
    );
}
add_action( 'plugins_loaded', 'my_plugin_load_textdomain' );
```

### File Structure
```
my-plugin/
├── languages/
│   ├── my-plugin.pot          (template)
│   ├── my-plugin-fr_FR.po     (editable source)
│   ├── my-plugin-fr_FR.mo     (compiled binary)
│   └── my-plugin-es_ES.po/mo
```

### ✅ Pros
- Historical WordPress standard
- Compatible with all tools (Poedit, Loco Translate)
- Works with translate.wordpress.org
- Well documented and supported

### ❌ Cons
- Binary `.mo` files (not version-control friendly)
- Requires compilation `.po` → `.mo`
- Performance: loads entire file into memory
- Not suitable for modern JavaScript

---

## 2️⃣ **Modern Method: JSON (for JavaScript) + `.mo` (for PHP)**

Since WordPress 5.0+ with Gutenberg:

### Code Example
```php
// PHP: classic method
load_plugin_textdomain( 'my-plugin', false, ... );

// JS: automatic JSON loading
wp_set_script_translations( 
    'my-script-handle', 
    'my-plugin', 
    plugin_dir_path( __FILE__ ) . 'languages' 
);
```

### File Structure
```
my-plugin/
├── languages/
│   ├── my-plugin.pot
│   ├── my-plugin-fr_FR.po
│   ├── my-plugin-fr_FR.mo                    (for PHP)
│   ├── my-plugin-fr_FR-{hash}.json           (for JS)
│   └── my-plugin-fr_FR-{hash2}.json          (one per JS script)
```

### JSON Format
```json
{
  "domain": "my-plugin",
  "locale_data": {
    "my-plugin": {
      "": {
        "domain": "my-plugin",
        "lang": "fr_FR"
      },
      "Hello World": ["Bonjour le monde"]
    }
  }
}
```

### ✅ Pros
- **JS Performance**: loads only necessary translations
- Supports React, Vue, Angular
- Human-readable and version-control friendly JSON files
- Automatic split by JS script

### ❌ Cons
- Dual management: `.mo` for PHP + `.json` for JS
- Hash in filenames (complex to manage)
- **Requires WP-CLI** (`wp i18n make-json`) ← **This is where i18n-404-tools comes in!**

---

## 3️⃣ **WordPress.org Method (GlotPress)**

For plugins hosted on WordPress.org:

### Code Example
```php
// Minimalist: just declare text domain
load_plugin_textdomain( 'my-plugin' );
```

### Workflow
1. Generate `.pot` and commit it
2. WordPress.org automatically extracts strings
3. Translators translate on translate.wordpress.org
4. WordPress automatically downloads `.mo`/`.json` from WP servers

### ✅ Pros
- **Zero translation maintenance**
- Global translator community
- Automatic updates
- No need to commit `.po`/`.mo` files

### ❌ Cons
- Reserved for WordPress.org plugins
- Dependency on WordPress servers
- Delay between string creation and translation availability

---

## 📊 Comparison Table

| Criterion | Classic `.po`/`.mo` | Modern JSON | GlotPress (WP.org) |
|-----------|-------------------|-------------|-------------------|
| **PHP** | ✅ Perfect | ✅ Perfect | ✅ Perfect |
| **JavaScript** | ⚠️ Heavy | ✅ Optimal | ✅ Optimal |
| **Performance** | ⚠️ Loads all | ✅ Split per script | ✅ Split per script |
| **Tools** | ✅ All | ⚠️ WP-CLI required | ✅ Automatic |
| **Version Control** | ❌ Binary files | ✅ Readable JSON | ✅ No files |
| **Maintenance** | ⚠️ Manual | ⚠️ Manual | ✅ Automatic |

---

## 🎯 Current Trend (2025-2026)

**Clear direction**: **JSON for JS + GlotPress for distribution**

WordPress is moving towards:
- ✅ Removing `.mo` files from repositories (automatic download)
- ✅ Systematic JSON for all JavaScript code
- ✅ GlotPress as distribution standard
- ✅ Only `.pot` files in repository

---

## 💡 The Role of **i18n-404-tools**

The **i18n-404-tools** plugin fills a critical gap in the modern localization workflow by providing:

### 🔧 What i18n-404-tools Does
- ✅ **Generate `.pot` files** without WP-CLI access
- ✅ **Generate JSON translation files** from `.po` files (the "WP-CLI required" step!)
- ✅ One-click i18n maintenance directly from WordPress admin
- ✅ No shell access or command-line tools needed

### 🎯 Use Case
**For developers who want modern JSON translations but:**
- Don't have WP-CLI installed
- Don't have shell/SSH access
- Want to stay in the WordPress admin interface
- Need a GUI alternative to `wp i18n make-json`

---

## 🔄 Recommended Workflow with i18n-404-tools

### For Plugins Not Yet on WordPress.org (Hybrid Approach)

This approach ensures compatibility with both GitHub distribution and future WordPress.org hosting:
```php
// Load both .mo (PHP) and JSON (JS)
load_plugin_textdomain( 'my-plugin', false, ... );
wp_set_script_translations( 'my-script', 'my-plugin', ... );
```

### File Structure (Commit All Translation Files)
```
my-plugin/
├── languages/
│   ├── my-plugin.pot              ← Generated by i18n-404-tools
│   ├── my-plugin-fr_FR.po         ← Edited with Poedit/Loco Translate
│   ├── my-plugin-fr_FR.mo         ← Generated by i18n-404-tools
│   └── my-plugin-fr_FR-{hash}.json ← Generated by i18n-404-tools
```

### Workflow Steps
1. **Generate `.pot`** using i18n-404-tools admin interface
2. **Translate** using Poedit, Loco Translate, or any `.po` editor
3. **Generate `.mo` and `.json`** using i18n-404-tools admin interface
4. **Commit all files** (`.pot`, `.po`, `.mo`, `.json`) to repository

### Why This Approach?
- ✅ Works immediately when installed from GitHub
- ✅ Easy transition to WordPress.org later (just stop committing `.mo`/`.json`)
- ✅ No dependency on external servers
- ✅ Users get translations immediately after install

---

## 🚀 Future Migration Path

When moving to WordPress.org:
1. Keep generating `.pot` with i18n-404-tools
2. Stop committing `.mo` and `.json` files
3. Let translate.wordpress.org handle distribution
4. Update `.gitignore` to exclude translation files except `.pot`

---

## 📚 Summary

| Method | Best For | Tools Needed |
|--------|----------|--------------|
| **Classic** | Legacy plugins, PHP-only | Poedit, Loco Translate |
| **Modern (JSON)** | Gutenberg blocks, React apps | **i18n-404-tools** or WP-CLI |
| **GlotPress** | WordPress.org plugins | None (automatic) |
| **Hybrid** (recommended for i18n-404-tools) | GitHub + future WP.org | **i18n-404-tools** |

**i18n-404-tools makes modern WordPress localization accessible to everyone, regardless of hosting environment or technical expertise.**