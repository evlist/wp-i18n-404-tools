=== Missing i18n Tools ===
Contributors: evlist
Donate link: https://e.vli.st/
Tags: i18n, translation, pot, json, gettext
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.1.0
Requires PHP: 8.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Generate POT and JSON translation files for any installed plugin directly from the admin interface—no shell_exec, no external WP-CLI required.

== License compliance ==

This plugin is distributed under the GPLv3 or later license. It uses several Composer dependencies and includes vendored code from the upstream project [wp-cli/i18n-command](https://github.com/wp-cli/i18n-command) (MIT license).

All license and copyright information for dependencies is tracked in the repository using the REUSE specification, via the `.reuse/dep5` file at the root of the source repository. This file is not included in plugin releases, but ensures full traceability and compliance for developers and auditors.

Vendored code from `wp-cli/i18n-command` is located in `admin/wp-cli/src/` and is not modified except for license headers. Composer dependencies are managed via `composer.json` and `composer.lock`.

For more details, see the repository documentation and compliance files.

WordPress internationalization tools: generate POT and JSON translation files from any installed plugin directly from the admin interface.


== Description ==

⚠️ Project Status: On Standby > This project was designed to bridge the gap between WP-CLI and the WordPress dashboard for modern i18n workflows. Since Loco Translate now natively supports these features (JSON generation and JS text extraction), this plugin is currently on standby.

Easily generate POT and JSON translation files for any plugin, directly from the WordPress admin—no command-line or WP-CLI required.

Missing i18n Tools provides WordPress internationalization features that are missing from the WordPress admin interface and common free plugins. It allows you to generate POT (Portable Object Template) files and JSON translation files for any installed plugin directly from your WordPress admin, without needing command-line access or WP-CLI.

= Key Features =

* **Generate POT files**: Create translation template files from any installed plugin with one click
* **Generate JSON translations**: Create JavaScript translation files for block editor and modern JavaScript
* **Extract JavaScript strings**: Unlike other plugins like Loco Translate, this plugin extracts localizable strings from JavaScript files (.js), not just PHP code
* **User-friendly interface**: Simple modal dialogs guide you through the process
* **Progress tracking**: Real-time progress display shows file size and update status
* **Multilingual ready**: The plugin itself is fully translatable and includes French and Chinese translations
* **No command-line needed**: All operations work directly from the WordPress admin interface

= Technical Requirements =


**Important**: This plugin generates POT and JSON files by directly using PHP classes borrowed from the official [wp-cli/i18n-command](https://github.com/wp-cli/i18n-command) project. You do not need to install WP-CLI or enable any shell functions. All operations are performed internally in PHP for maximum compatibility and security.

= Use Cases =

* Plugin developers who need to extract translatable strings from their code (including JavaScript)
* Translators who need fresh POT files to update translations
* Site administrators managing multiple plugins with custom translations
* Anyone working in environments without shell/SSH access

= Technical Details =

All operations respect WordPress coding standards and follow security best practices with proper nonce verification and capability checks.

= Translation of the Plugin =

This plugin was translated into French and Chinese using itself! The translation workflow involved:

1. Using **Missing i18n Tools** itself to generate the initial POT template file
2. Using **[Loco Translate](https://localise.biz/)** to edit translations and generate the compiled `.mo` and `.l10n.php` files
3. Iterating and improving translations based on context

This demonstrates the plugin's practical utility and the power of combining it with translation management tools like Loco Translate.

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins → Add New
3. Search for "Missing i18n Tools"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Go to Plugins → Add New → Upload Plugin
4. Choose the downloaded ZIP file and click "Install Now"
5. Activate the plugin through the 'Plugins' menu

= After Activation =

Once activated, you'll see new action links ("Generate .pot" and "Generate JSON") next to each plugin in your Plugins list. The plugin will automatically download WP-CLI on first use if it's not already present.

== Frequently Asked Questions ==

= Do I need WP-CLI installed on my server? =

No! The plugin uses WP-CLI classes internally without running WP-CLI. You don't need shell access or any command-line tools.

= Which plugins can I generate translations for? =

You can generate POT and JSON files for any installed plugin. The plugin must follow WordPress coding standards for translatable strings (using `__()`, `_e()`, etc.).

= Does this extract strings from JavaScript files? =

Yes! Unlike some other translation plugins, this tool extracts localizable strings not only from PHP code but also from JavaScript files. This is especially important for modern plugins using the block editor or React-based interfaces.

= Where are the generated files saved? =

Generated files are saved in the target plugin's `/languages/` directory. POT files use the format `plugin-slug.pot` and JSON files use the format `plugin-slug-{locale}-{hash}.json`.

= Does this work with block editor / Gutenberg? =

Yes! The "Generate JSON" feature specifically creates JSON translation files needed for translating JavaScript strings in the block editor and modern React-based interfaces.

= What permissions do I need? =

You need the `manage_options` capability (typically Administrator role) to use this plugin's features.

= Is this plugin secure? =

Yes. All AJAX requests are protected with WordPress nonces and capability checks. The plugin follows WordPress coding standards and security best practices.

= Can I translate this plugin itself? =

Absolutely! The plugin is fully translatable. You can use Missing i18n Tools to generate a fresh POT file, then use tools like [Loco Translate](https://localise.biz/) to manage your translations. This is exactly how the French and Chinese translations were created!

== Screenshots ==

1. Plugin action links in the Extensions page showing "Generate .pot" and "Generate JSON" options with icons.
2. Modal dialog for generating .pot (portable template) files with file size and update information.
3. Modal dialog for generating JSON translation files with automatic outdated detection.
4. Modal dialog for generating .pot files displayed in Chinese (zh_CN) translation.

== Changelog ==

= 1.1.0 =
* Refactoring to use vendored WP-CLI i18n classes instead of shell_exec.

= 1.0.0 =
* Initial release
* Generate POT (Portable Object Template) files for any plugin
* Generate JSON translation files for JavaScript internationalization
* Extract localizable strings from both PHP and JavaScript files
* Modal interface with progress tracking
* Automatic WP-CLI management
* French (fr_FR) translation included
* Chinese (zh_CN) translation included
* Full REUSE/SPDX compliance
* WordPress Coding Standards compliant

== Upgrade Notice ==

= 1.1.0 =
Full compatibility.

= 1.0.0 =
Initial release of Missing i18n Tools. Generate POT and JSON translation files directly from WordPress admin, including strings from JavaScript files!

