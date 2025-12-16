#!/usr/bin/env bash
# Automate WordPress install + DB creation + admin user + plugins (Loco Translate + local plugin)
# Usage:
#   WORDPRESS_CONTAINER=<container-name-or-id> .devcontainer/bin/wp-auto-install.sh
# Environment variables (all optional, sensible defaults provided):
#   WORDPRESS_CONTAINER  - docker container name for the wordpress service (auto-detected if empty)
#   WP_PATH              - WP files path inside the container (default: /var/www/html)
#   SITE_URL             - Site URL used for wp core install (default: http://localhost)
#   SITE_TITLE           - Site title (default: "WordPress")
#   ADMIN_USER           - Admin username (default: admin)
#   ADMIN_PASS           - Admin password (default: password)
#   ADMIN_EMAIL          - Admin email (default: admin@example.test)
#   DB_NAME              - Database name (default: wordpress)
#   DB_USER              - DB user (default: root)
#   DB_PASS              - DB password (default: root)
#   DB_HOST              - DB host (default: db:3306)
# Notes:
# - This script requires wp-cli to be present inside the wordpress container.
# - It talks to the wordpress container via `docker exec`. Make sure the devcontainer has access to the host docker socket
#   (or run this script on the host).
set -euo pipefail

: "${WP_PATH:=/var/www/html}"
: "${SITE_URL:=http://localhost}"
: "${SITE_TITLE:=WordPress}"
: "${ADMIN_USER:=admin}"
: "${ADMIN_PASS:=password}"
: "${ADMIN_EMAIL:=admin@example.test}"
: "${DB_NAME:=wordpress}"
: "${DB_USER:=root}"
: "${DB_PASS:=root}"
: "${DB_HOST:=db:3306}"
: "${WP_CLI_ALLOW_ROOT:=--allow-root}"
RETRIES=20
SLEEP=3

# Auto-detect wordpress container if not provided
if [ -z "${WORDPRESS_CONTAINER:-}" ]; then
  WORDPRESS_CONTAINER="$(docker ps --filter 'label=com.docker.compose.service=wordpress' --format '{{.Names}}' | head -n1 || true)"
  if [ -z "$WORDPRESS_CONTAINER" ]; then
    echo "ERROR: Could not auto-detect wordpress container. Set WORDPRESS_CONTAINER environment variable."
    exit 1
  fi
fi

echo "Using wordpress container: $WORDPRESS_CONTAINER"
echo "WP path: $WP_PATH"
echo "DB host: $DB_HOST"

# Convenience wrapper for running wp inside container as root so wp can write files
run_wp() {
  local cmd="$1"
  docker exec -u root -i "$WORDPRESS_CONTAINER" bash -lc "wp $cmd --path='${WP_PATH}' ${WP_CLI_ALLOW_ROOT}"
}

# Check wp-cli exists in container
if ! docker exec -u root "$WORDPRESS_CONTAINER" bash -lc 'command -v wp >/dev/null 2>&1'; then
  echo "ERROR: wp-cli not found inside container $WORDPRESS_CONTAINER."
  echo "Either install wp-cli in the container or run wp-cli from the host and mount the WP files."
  exit 2
fi

# If WP is already installed, just ensure plugins/users are present
if docker exec -u root "$WORDPRESS_CONTAINER" bash -lc "wp core is-installed --path='${WP_PATH}' ${WP_CLI_ALLOW_ROOT}" >/dev/null 2>&1; then
  echo "WordPress already installed. Skipping core install."
else
  echo "Creating wp-config.php"
  docker exec -u root "$WORDPRESS_CONTAINER" bash -lc "wp config create --dbname='${DB_NAME}' --dbuser='${DB_USER}' --dbpass='${DB_PASS}' --dbhost='${DB_HOST}' --path='${WP_PATH}' ${WP_CLI_ALLOW_ROOT}"

  echo "Waiting for DB to be available and creating database (retries: ${RETRIES})..."
  n=0
  until docker exec -u root "$WORDPRESS_CONTAINER" bash -lc "wp db create --path='${WP_PATH}' ${WP_CLI_ALLOW_ROOT}" >/dev/null 2>&1; do
    n=$((n+1))
    if [ "$n" -ge "$RETRIES" ]; then
      echo "ERROR: Unable to create database after $RETRIES attempts."
      echo "Last attempted: wp db create --path=${WP_PATH}"
      exit 3
    fi
    echo "DB unavailable yet, sleeping ${SLEEP}s (attempt $n/${RETRIES})..."
    sleep "$SLEEP"
  done
  echo "Database created."

  echo "Installing WordPress core..."
  docker exec -u root "$WORDPRESS_CONTAINER" bash -lc "wp core install --url='${SITE_URL}' --title='${SITE_TITLE}' --admin_user='${ADMIN_USER}' --admin_password='${ADMIN_PASS}' --admin_email='${ADMIN_EMAIL}' --skip-email --path='${WP_PATH}' ${WP_CLI_ALLOW_ROOT}"
  echo "WordPress installed."
fi

# Ensure admin user exists (create if missing or set password if exists)
if run_wp "user get '${ADMIN_USER}'" >/dev/null 2>&1; then
  echo "Admin user '${ADMIN_USER}' exists. Ensuring password and role."
  run_wp "user update '${ADMIN_USER}' --user_pass='${ADMIN_PASS}' --role=administrator"
else
  echo "Creating admin user '${ADMIN_USER}'."
  run_wp "user create '${ADMIN_USER}' '${ADMIN_EMAIL}' --user_pass='${ADMIN_PASS}' --role=administrator"
fi

# Install and activate Loco Translate from WP.org
if run_wp "plugin is-installed loco-translate" >/dev/null 2>&1; then
  echo "loco-translate already installed."
else
  echo "Installing loco-translate plugin from WordPress.org..."
  run_wp "plugin install loco-translate --activate"
fi

# Activate local plugin (assumes plugin files are already present in wp-content/plugins/wp-i18n-404-tools)
LOCAL_PLUGIN_SLUG="wp-i18n-404-tools"
if run_wp "plugin is-installed ${LOCAL_PLUGIN_SLUG}" >/dev/null 2>&1; then
  echo "Local plugin '${LOCAL_PLUGIN_SLUG}' found."
  run_wp "plugin activate ${LOCAL_PLUGIN_SLUG}"
else
  echo "ERROR: Local plugin '${LOCAL_PLUGIN_SLUG}' not found in wp-content/plugins."
  echo "Make sure the plugin is present in the repo and mounted into ${WP_PATH}/wp-content/plugins."
  exit 4
fi

echo "Installation complete. Admin user: ${ADMIN_USER} (password: ${ADMIN_PASS})"
echo "You can now run: docker exec -it ${WORDPRESS_CONTAINER} bash -lc 'wp plugin list --path=${WP_PATH} --allow-root'"
