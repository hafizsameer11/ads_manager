# Dockerfile for Laravel application (PHP-FPM)
# Builds a PHP-FPM container with Composer and optional Node (for asset builds).

FROM php:8.2-fpm

ARG APP_ENV=production
ENV APP_ENV=${APP_ENV}

WORKDIR /var/www/html

# Install system dependencies, PHP extensions and Node/npm
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       git curl unzip libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libxml2-dev \
       zlib1g-dev nodejs npm ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql mbstring exif pcntl bcmath gd intl xml zip \
    && pecl install redis || true \
    && docker-php-ext-enable redis || true \
    && rm -rf /var/lib/apt/lists/*

# Install Composer from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock* ./

# Install PHP deps (vendor). Use --no-dev for production builds.
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress --no-suggest --no-dev || composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy the application code
COPY . .

# If project has frontend tooling, build assets (optional)
RUN if [ -f package.json ]; then npm ci && npm run build || true; fi

# Ensure storage & cache dirs are writable
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 9000

# Default command: run PHP-FPM
CMD ["php-fpm"]
