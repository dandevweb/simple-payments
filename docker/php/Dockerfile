FROM php:8.4-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl git libpq-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP settings
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
