#!/usr/bin/env bash
# Bootstrapping helper — attempts to install WP via WP-CLI inside the running wordpress container,
# then activates the plugin. If docker-compose is not available inside the container, the script
# will print the exact commands to run manually.

set -e
COMPOSE_FILE=".devcontainer/docker-compose.yml"
DC="docker-compose -f ${COMPOSE_FILE}"

echo "Waiting for WordPress & MySQL to become ready (timeout ~120s)..."
# Wait for MySQL and WordPress container to be up by polling the WP HTTP endpoint
RETRIES=24
SLEEP=5
for i in $(seq 1 $RETRIES); do
  if curl -sSfI http://localhost:8080 >/dev/null 2>&1; then
    echo "WordPress HTTP endpoint responding."
    break
  fi
  echo "Waiting... ($i/$RETRIES)"
  sleep $SLEEP
done

# Verify that docker-compose is callable from the workspace. If not, print manual steps and exit.
if ! command -v docker-compose >/dev/null 2>&1; then
  echo
  echo "Note: docker-compose is not available inside this container."
  echo "Run these commands in Codespace terminal (they will run on the Codespaces host):"
  echo
  echo "  docker-compose -f ${COMPOSE_FILE} exec -T wordpress wp core is-installed || \"
  echo "    docker-compose -f ${COMPOSE_FILE} exec -T wordpress wp core install --url='http://localhost:8080' --title='Codespace WP' --admin_user=admin --admin_password=admin --admin_email=dev@localhost --skip-email"
  echo
  echo "  docker-compose -f ${COMPOSE_FILE} exec -T wordpress wp plugin activate wp-i18n-404-tools || true"
  echo
  exit 0
fi

# If we have docker-compose, run installation steps automatically:
if ${DC} ps >/dev/null 2>&1; then
  # Install WP (if not installed)
  if ! ${DC} exec -T wordpress wp core is-installed >/dev/null 2>&1; then
    echo "Installing WordPress..."
    ${DC} exec -T wordpress wp core install --url='http://localhost:8080' --title='Codespace WP' --admin_user=admin --admin_password=admin --admin_email=dev@localhost --skip-email
  else
    echo "WordPress already installed."
  fi

  # Activate the plugin (plugin folder name is assumed to be wp-i18n-404-tools)
  echo "Activating plugin (if present)..."
  ${DC} exec -T wordpress wp plugin activate wp-i18n-404-tools || echo "Plugin activation failed or plugin not present."
else
  echo "docker-compose services are not running. Start them (from repo root):"
  echo "  docker-compose -f ${COMPOSE_FILE} up -d"
fi
