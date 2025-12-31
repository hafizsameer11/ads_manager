# Dockerfile for Laravel application (PHP-FPM)
# Builds a PHP-FPM container with Composer and optional Node (for asset builds).

FROM php:8.2-apache

ARG APP_ENV=production
ENV APP_ENV=${APP_ENV}

WORKDIR /var/www/html

# Install system dependencies, PHP extensions and Node/npm, enable Apache rewrite
RUN apt-get update \
     && apt-get install -y --no-install-recommends \
         git curl unzip libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libxml2-dev \
         zlib1g-dev nodejs npm ca-certificates \
     && docker-php-ext-configure gd --with-freetype --with-jpeg \
     && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql mbstring exif pcntl bcmath gd intl xml zip \
     && pecl install redis || true \
     && docker-php-ext-enable redis || true \
     && a2enmod rewrite \
     && rm -rf /var/lib/apt/lists/*

# Install Composer from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock* ./

# Install PHP deps without running composer scripts (artisan not present yet).
# This speeds up rebuilds while avoiding post-autoload scripts that expect the app files.
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress --no-suggest --no-dev --no-scripts || \
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-suggest --no-dev --no-scripts

# Copy the application code (now `artisan` and app files are present)
COPY . .

# Run Composer scripts and Laravel package discovery now that the app is copied
RUN composer dump-autoload --optimize && php artisan package:discover --ansi || true

# If project has frontend tooling, build assets (optional)
RUN if [ -f package.json ]; then npm ci && npm run build || true; fi

# Ensure storage & cache dirs are writable
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Serve Laravel's public directory as Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!/var/www/html/!g' /etc/apache2/apache2.conf \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf || true

EXPOSE 80

# Default command: run Apache in foreground
CMD ["apache2-foreground"]
