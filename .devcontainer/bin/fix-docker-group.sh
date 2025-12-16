#!/usr/bin/env bash
set -euo pipefail

SOCK=/var/run/docker.sock
USER=vscode

if [ ! -S "$SOCK" ]; then
  echo "Socket $SOCK not found or not a socket; nothing to do."
  exit 0
fi

GID=$(stat -c '%g' "$SOCK")
# See if there's already a group with that GID
GROUP_NAME=$(getent group "$GID" | cut -d: -f1 || true)

if [ -n "$GROUP_NAME" ]; then
  echo "Found existing group '$GROUP_NAME' with gid $GID; adding $USER to it."
  sudo usermod -aG "$GROUP_NAME" "$USER" || true
else
  # If 'docker' exists but with a different gid, create an alternate name; otherwise create docker with the host GID
  if getent group docker >/dev/null 2>&1; then
    ALT_NAME="dockerhost"
    echo "Group 'docker' exists with different gid. Creating group '$ALT_NAME' with gid $GID and adding $USER."
    sudo groupadd -g "$GID" "$ALT_NAME" || true
    sudo usermod -aG "$ALT_NAME" "$USER" || true
  else
    echo "Creating group 'docker' with gid $GID and adding $USER."
    sudo groupadd -g "$GID" docker || true
    sudo usermod -aG docker "$USER" || true
  fi
fi

# Fix workspace ownership so vscode can write files if needed
sudo chown -R "$USER":"$USER" /workspaces/wp-i18n-404-tools || true

echo "Done. New group membership added for $USER (gid $GID)."
echo "Open a new terminal or rebuild/reopen the devcontainer so group membership takes effect."
