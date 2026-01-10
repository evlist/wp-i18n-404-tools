=== Missing i18n Tools ===
Contributors: evlist
Donate link: https://e.vli.st/
Tags: i18n, translation, pot, json, gettext
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

WordPress internationalization tools: generate POT and JSON translation files from any installed plugin directly from the admin interface.

== Description ==

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

**Important**: This plugin needs to execute **WP-CLI** to generate POT and JSON files, but you do not need to install WP-CLI yourself. The plugin automatically downloads and manages a local `wp-cli.phar` in `/wp-content/uploads/i18n-404-tools/` (protected by `.htaccess`).

For execution, the PHP function `shell_exec()` must be enabled by your hosting provider. If your server blocks `shell_exec()`, you may need to:
* Ask your hosting provider to enable `shell_exec()` (and allow running the local `wp-cli.phar`)
* Use a hosting plan that supports WP-CLI execution (common on managed WordPress hosts)
* Check your control panel for WP-CLI/Shell settings if available

= Use Cases =

* Plugin developers who need to extract translatable strings from their code (including JavaScript)
* Translators who need fresh POT files to update translations
* Site administrators managing multiple plugins with custom translations
* Anyone working in environments without shell/SSH access

= Technical Details =

The plugin automatically downloads and manages WP-CLI (WordPress Command Line Interface) internally to perform translation operations. It uses WordPress AJAX for smooth user experience and supports both traditional PHP translations and modern JavaScript translations (JED format).

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

No! The plugin automatically downloads and manages WP-CLI internally. You don't need shell access or any command-line tools.

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

= 1.0.0 =
Initial release of Missing i18n Tools. Generate POT and JSON translation files directly from WordPress admin, including strings from JavaScript files!

