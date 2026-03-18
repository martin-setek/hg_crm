FROM php:8.3-cli

# Install extensions
RUN apt-get update && apt-get install -y \
    unzip git curl \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN mkdir -p storage/framework/{sessions,views,cache/data} storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache \
    && touch database/database.sqlite \
    && chmod 666 database/database.sqlite

# Run migrations + seed at container start
RUN php artisan migrate --force --seed 2>/dev/null || true

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
