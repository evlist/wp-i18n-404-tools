# Use VS Code devcontainer PHP image as base
FROM mcr.microsoft.com/vscode/devcontainers/php:0-8.1

# Install utilities, composer is present in base image
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
		docker.io \
		docker-compose-plugin \
  && rm -rf /var/lib/apt/lists/*

# (Optional) create a non-root group mapping if you want; with DOCKER_HOST to tcp this may not be needed
USER vscode

USER root

# Ensure Xdebug is enabled if present (image may already include)
RUN if php -m | grep -q xdebug; then echo 'xdebug present'; fi

# Create workspace folder and set permissions
RUN mkdir -p /workspaces/wp-i18n-404-tools
WORKDIR /workspaces/wp-i18n-404-tools

# Drop back to vscode user
USER vscode
