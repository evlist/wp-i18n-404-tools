<!--
SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>

SPDX-License-Identifier: GPL-3.0-or-later
-->
# Project Context: wp-i18n-404-tools

I'm working on the `no-shell-exec` branch of a WordPress plugin called **i18n-404-tools**.

## Plugin Description
This WordPress plugin provides missing internationalization (i18n) tools directly from the admin interface. It allows you to:
- Generate `.pot` (Portable Object Template) files
- Regenerate translation JSON files
- Copy WP-CLI command outputs
- Perform one-click i18n maintenance without leaving the browser

## Technical Architecture
- **Primary language**: PHP 8.0+
- **Framework**: WordPress 6.9 (tested)
- **Critical feature**: The plugin directly uses PHP classes from the [wp-cli/i18n-command](https://github.com/wp-cli/i18n-command) project WITHOUT requiring WP-CLI installation or shell access. No `shell_exec` function is used.
- **License**: GPL-3.0-or-later, REUSE/SPDX compliant
- **CI/CD**: GitHub Actions configured

## Project Structure
```
.
├── i18n-404-tools/       # Main plugin code
├── tests/                # PHPUnit tests
├── scripts/              # Build scripts
├── docs/                 # Documentation (DEVELOPERS.md, FAQ.md)
├── .devcontainer/        # Codespaces configuration
└── .github/workflows/    # CI/CD Actions
```

## Current Objectives
- Working on the `no-shell-exec` branch which eliminates all `shell_exec` dependencies
- Plugin pending approval on WordPress.org (submitted January 10, 2026)
- Complementary to Loco Translate (for .po/.mo editing)

## Code Standards & Compliance

### Strict Requirements
1. **REUSE Compliance**: All files must have proper SPDX license headers
2. **WordPress Standards**: Code must pass WordPress Coding Standards (PHPCS) and `wp plugin check`
3. **No Cheating**: Never use `phpcs:ignore` or similar suppressions
4. **Security Best Practices**: Follow WordPress security guidelines (escaping, sanitization, nonces, capability checks)
5. **Internationalization**: All user-facing strings must be translatable using WordPress i18n functions

### Protected Files
**NEVER modify these directories** - they contain vendored/copied library code:
- `vendor/`
- `wp-cli/src/`

## Structural Integrity & PHP Placement
- **No Method Nesting:** Always ensure that a new or modified method is placed outside of other methods. Check closing braces `}` before insertion.
- **Namespace Awareness:** Maintain strict adherence to the defined `namespace` and `use` statements. Do not re-declare or omit them.
- **Indentation Consistency:** Use the project's standard (Tabs for WordPress) to prevent the diff engine from misaligning the code.
- **Contextual Anchors:** If a file is large, provide the code within its immediate class context to help the IDE's insertion tool locate the correct line.

## Internationalization (i18n) & Localization (l10n)
- **Modern WP Standards:** Always use WordPress i18n functions. Never hardcode strings.
- **PHP i18n:** Use `__()`, `_e()`, `_x()`, or `_n()`. Always include the correct text domain (`i18n-404-tools`, defined in project headers).
- **JavaScript i18n:** Use the `@wordpress/i18n` package (e.g., `__('string', 'domain')`). 
- **Script Localization:** When registering scripts, always use `wp_set_script_translations()` for any JS file that contains translatable strings.
- **JSON Compatibility:** Ensure that translatable strings in JS are compatible with `wp-cli make-json` extraction. Do not use dynamic variables inside translation functions (e.g., `__( variable, domain )` is forbidden).

### Language Guidelines
- **Code comments & documentation**: English only
- **Developer communication**: French or English accepted

## Automated Log Inspection
- **Direct Log Access:** The WordPress debug log is located at `/var/www/html/wp-content/debug.log`. You have read access to this file.
- **Proactive Tail:** If I describe a bug or a "white screen", start by reading the last 20-50 lines of this file to find relevant PHP Notices, Warnings, or Fatal Errors.
- **Hook & i18n Debugging:** Look specifically for "Mismatched text domain" or "Missing .mo file" entries in this log when troubleshooting localization.
- **Context Correlation:** Compare the timestamps in the log with my current issue to ensure you are analyzing the correct error burst.

## Debugging & Xdebug First
- **Stop Guessing:** If a bug depends on dynamic WordPress data (like Hook priority, Global variables, or Query results), do not guess the values.
- **Suggest Breakpoints:** Instead of proposing multiple code fixes, suggest 2-3 strategic breakpoints for Xdebug. Specify the file and line number.
- **Variable Inspection:** Tell me exactly which variables to inspect in the "Watch" or "Variables" panel (e.g., `$wp_query`, `$post`, or your Class properties).
- **Interactive Debugging:** Before rewriting a method, ask me: "Could you check the value of [Variable] at line [X]? This would confirm if [Assumption] is correct."

## What I Expect from You, Copilot
- Respect WordPress standards and project conventions
- Never suggest `shell_exec`, `exec`, `system`, or shell commands
- Prefer direct use of PHP classes from wp-cli/i18n-command
- Maintain WordPress 6.9+ compatibility
- Respect GPL-3.0-or-later and REUSE compliance
- Always include proper SPDX headers in new files
- Never bypass PHPCS rules - fix issues properly instead
- Apply security best practices (escape output, sanitize input, verify nonces and capabilities)