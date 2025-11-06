FROM wordpress:6.4-php8.1-apache

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

# Add custom Xdebug configuration (picked up from conf.d)
COPY xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

# Make sure files are owned by www-data (keeps permissions sane)
RUN chown -R www-data:www-data /var/www/html

USER www-data
