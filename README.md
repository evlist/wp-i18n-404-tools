## Open in Codespaces

Start a Codespace preconfigured with the devcontainer in this repository:

[![Open in Codespaces](https://github.com/codespaces/badge.svg)](https://github.com/codespaces/new?repo=evlist/wp-i18n-404-tools&ref=main&devcontainer_path=.devcontainer)

# Codespace / Devcontainer for wp-i18n-404-tools

What this adds
- A Codespace-friendly devcontainer that starts:
  - workspace container (the VS Code remote)
  - wordpress container (WordPress core on Apache)
  - db container (MySQL)
- Mounts the repository into WordPress plugins directory so you can edit the plugin live.
- Installs PHP Intelephense and sets WordPress stubs for WP-aware code completion.

How to use (quick)
1. Commit the `.devcontainer/` and `.vscode/` files and push to GitHub.
2. Create a Codespace for this repository (GitHub → Code → Codespaces → New codespace).
3. The Codespace devcontainer will build. It will try to run `composer install` and then run `./scripts/bootstrap-wp.sh`.
   - If `bootstrap-wp.sh` prints manual commands (because docker-compose isn't available in-container), open a Codespace terminal (bash) and run the printed docker-compose commands from the repository root.
4. Open http://localhost:8080 in the Codespaces forwarded ports (or use the Codespaces "Ports" view) — this is your WordPress site.
5. WP admin:
   - If bootstrap installed WP, login at /wp-admin with the admin credentials in the script (admin / admin).
   - Activate the plugin "wp-i18n-404-tools" in Plugins (it should appear since repo is mounted into plugins folder).
6. Edit code in VS Code — Intelephense will index vendor/php-stubs/wordpress-stubs and provide WP-aware completion.

Notes & tips
- To regenerate stubs / update dependencies: run `composer install` in the Codespace terminal.
- If you want to enable Xdebug, forward port 9003 and configure your IDE launch.json accordingly.
- The bootstrap script is conservative: if WP is already installed it will not overwrite.
