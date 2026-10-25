# Laravel backend served by "php artisan serve".
# Base image uses an exact version (never "latest").
FROM php:8.4.27-cli

LABEL org.opencontainers.image.source="https://github.com/aarmeliaa/uts-pl-24-538562-sv-24509"

# unzip is used by Composer; pdo_mysql lets Laravel talk to MySQL.
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Composer binary copied from the official Composer image (same version as CI).
COPY --from=composer:2.9.2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 1) Dependencies first. This layer stays cached until composer.json or
#    composer.lock change, so editing app code does not reinstall vendor/.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader

# 2) Application code, then the optimized autoloader.
COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && chmod +x docker/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache

# No secrets here: APP_KEY and DB_* come from environment variables at runtime.
USER www-data

EXPOSE 8000

ENTRYPOINT ["docker/entrypoint.sh"]
