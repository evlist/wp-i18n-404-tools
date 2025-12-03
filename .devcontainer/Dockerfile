# Use VS Code devcontainer PHP image as base
FROM mcr.microsoft.com/vscode/devcontainers/php:0-8.1

# Install utilities, composer is present in base image
USER root

RUN \
	# Add Docker's official GPG key and apt repository (keyrings location avoids warnings)
    mkdir -p /etc/apt/keyrings; \
    curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg; \
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian $(lsb_release -cs) stable" \
      > /etc/apt/sources.list.d/docker.list; \
    chmod a+r /etc/apt/keyrings/docker.gpg; \
    \
    # Update once and install Docker engine/cli/containerd + compose plugin and other utilities
    apt-get update && \
    apt-get install -y  \
		less \
		unzip \
		wget \
		git \
		ca-certificates \
		curl \
		apt-transport-https \
		gnupg \
		lsb-release \
  		containerd.io \
		docker-ce \
		docker-ce-cli \
		docker-compose-plugin && \
    rm -rf /var/lib/apt/lists/*

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
