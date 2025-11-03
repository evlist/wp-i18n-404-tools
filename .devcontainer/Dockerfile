# Use VS Code devcontainer PHP image as base
FROM mcr.microsoft.com/vscode/devcontainers/php:0-8.1

# Install utilities, WP-CLI, composer is present in base image
USER root

RUN apt-get update && apt-get install -y \
    less \
    unzip \
    wget \
    git \
  && rm -rf /var/lib/apt/lists/*

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
