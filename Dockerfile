FROM composer:2.8 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

FROM composer:2.8 AS vendor_dev
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist --no-scripts

FROM node:24.11-bookworm-slim AS assets
WORKDIR /app
COPY package*.json ./
RUN npm install --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM nginx:1.28.0-alpine3.21 AS web
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY public /var/www/html/public
COPY --from=assets /app/public/build /var/www/html/public/build

FROM php:8.4-fpm-bookworm AS runtime
ARG APP_UID=1000
ARG APP_GID=1000
RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libjpeg62-turbo-dev libpng-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl opcache pcntl pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*
RUN groupmod -o -g ${APP_GID} www-data && usermod -o -u ${APP_UID} -g www-data www-data
WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=vendor /usr/bin/composer /usr/local/bin/composer
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
RUN touch .env \
    && mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
CMD ["php-fpm"]

FROM runtime AS test
USER root
COPY --from=vendor_dev --chown=www-data:www-data /app/vendor ./vendor
USER www-data
