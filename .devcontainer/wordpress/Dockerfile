ARG WP_VERSION=latest
FROM wordpress:${WP_VERSION}

# (Optional) metadata about the image
LABEL maintainer="evlist <vdv@dyomedea.com>" \
      description="Devcontainer WordPress image for wp-i18n-404-tools (build-time WP_VERSION=${WP_VERSION})"


# Install prerequisites for pecl build
USER root
RUN apt-get update && apt-get install -y --no-install-recommends \
    pkg-config \
    gcc \
    make \
    autoconf \
    libc-dev \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Xdebug via pecl and enable it
RUN pecl channel-update pecl.php.net \
 && pecl install xdebug \
 && docker-php-ext-enable xdebug

# Install WP-CLI (phar)
RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
  && chmod +x /usr/local/bin/wp \
  && wp --info || true

# Add custom Xdebug configuration (picked up from conf.d)
COPY xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

# Make sure files are owned by www-data (keeps permissions sane)
RUN chown -R www-data:www-data /var/www/html

USER www-data
