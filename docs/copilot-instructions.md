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

### Language Guidelines
- **Code comments & documentation**: English only
- **Developer communication**: French or English accepted

## What I Expect from You, Copilot
- Respect WordPress standards and project conventions
- Never suggest `shell_exec`, `exec`, `system`, or shell commands
- Prefer direct use of PHP classes from wp-cli/i18n-command
- Maintain WordPress 6.9+ compatibility
- Respect GPL-3.0-or-later and REUSE compliance
- Always include proper SPDX headers in new files
- Never bypass PHPCS rules - fix issues properly instead
- Apply security best practices (escape output, sanitize input, verify nonces and capabilities)