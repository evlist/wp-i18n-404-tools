#!/usr/bin/env bash
# Bootstrapping helper — attempts to install WP via WP-CLI inside the running wordpress container,
# then activates the plugin. This version checks the wordpress service by its compose hostname
# (http://wordpress) so it works from the workspace container in the compose network.

set -e
COMPOSE_FILE=".devcontainer/docker-compose.yml"
DC="docker-compose -f ${COMPOSE_FILE}"

# Helper: test HTTP on the wordpress service from the workspace container
echo "Waiting for WordPress (service 'wordpress') to become ready (timeout ~120s)..."
RETRIES=24
SLEEP=5
for i in $(seq 1 $RETRIES); do
  if curl -sSfI http://wordpress/ >/dev/null 2>&1; then
    echo "WordPress HTTP endpoint responding (via service name)."
    break
  fi
  echo "Waiting... ($i/$RETRIES)"
  sleep $SLEEP
done

# If docker-compose is not available inside the workspace container, print manual commands
if ! command -v docker-compose >/dev/null 2>&1; then
  echo
  echo "Note: docker-compose is not available inside this container."
  echo "Run these commands in Codespace terminal (they run on the Codespaces host):"
  echo
  echo "  docker-compose -f ${COMPOSE_FILE} exec -T wordpress wp core is-installed || \\"
  echo "    docker-compose -f ${COMPOSE_FILE} exec -T wordpress wp core install --url='http://localhost:8080' --title='Codespace WP' --admin_user=admin --admin_password=admin --admin_email=dev@localhost --skip-email"
  echo
  echo "  docker-compose -f ${COMPOSE_FILE} exec -T wordpress wp plugin activate wp-i18n-404-tools || true"
  echo
  exit 0
fi

# If docker-compose is available on this container, use it:
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
