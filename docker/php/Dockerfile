# syntax=docker/dockerfile:1.7

# ---- STAGE 1: Build Front-end Assets ----
FROM node:20-bookworm-slim AS builder

WORKDIR /var/www/html

COPY laravel_app/package.json laravel_app/package-lock.json ./
COPY laravel_app/vite.config.js ./

RUN --mount=type=cache,target=/root/.npm \
    npm ci --no-audit --no-fund

COPY laravel_app/. .

RUN npm run build \
 && rm -rf node_modules


# ---- STAGE 2: Final Production Image ----
FROM php:8.3-apache-bookworm

RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && apt-get install -y --no-install-recommends \
      zip unzip git curl \
      libpng-dev libonig-dev libxml2-dev libpq-dev postgresql-client \
      libicu-dev libzip-dev zlib1g-dev netcat-openbsd \
 && docker-php-ext-install -j"$(nproc)" zip pdo pdo_mysql pdo_pgsql intl mbstring exif pcntl bcmath gd \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# Pin major version supaya layer cache lebih stabil daripada composer:latest
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Cache Composer lebih dulu (bergantung composer.lock)
COPY --from=builder /var/www/html/composer.json /var/www/html/composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# Baru salin kode + hasil Vite build
COPY --from=builder /var/www/html .
COPY docker/php/apache.conf /etc/apache2/sites-available/000-default.conf

RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer dump-autoload --optimize --no-dev \
 && php artisan package:discover --ansi || true \
 && php artisan storage:link || true \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public

EXPOSE 80

CMD ["apache2-foreground"]
