# Use VS Code devcontainer PHP image as base
FROM mcr.microsoft.com/vscode/devcontainers/php:0-8.1

# Install utilities, WP-CLI, composer is present in base image
USER root

RUN apt-get update && apt-get install -y \
    less \
    unzip \
    wget \
    git \
		ca-certificates \
		curl \
		apt-transport-https \
		gnupg \
		lsb-release \
  && rm -rf /var/lib/apt/lists/*

# Install Docker CLI (docker.io) - simpler choice for Debian/Ubuntu bases
RUN apt-get update && apt-get install -y docker.io && rm -rf /var/lib/apt/lists/*

# Install docker-compose v2 (binary). Check latest release and arch if needed.
RUN curl -Lo /usr/local/bin/docker-compose \
      "https://github.com/docker/compose/releases/latest/download/docker-compose-linux-x86_64" \
    && chmod +x /usr/local/bin/docker-compose

# (Optional) create a non-root group mapping if you want; with DOCKER_HOST to tcp this may not be needed
USER vscode

# Install WP-CLI (phar)
RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
  && chmod +x /usr/local/bin/wp \
  && wp --info || true

# Ensure Xdebug is enabled if present (image may already include)
RUN if php -m | grep -q xdebug; then echo 'xdebug present'; fi

# Create workspace folder and set permissions
RUN mkdir -p /workspaces/wp-i18n-404-tools
WORKDIR /workspaces/wp-i18n-404-tools

# Drop back to vscode user
USER vscode
