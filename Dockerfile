FROM php:8.3-apache

# Extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev zip unzip git \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache config
RUN a2enmod rewrite
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN echo '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>' >> /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copy app
COPY . .

# Composer install
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN mkdir -p storage/framework/{sessions,views,cache/data} storage/logs bootstrap/cache database \
    && touch database/database.sqlite \
    && chmod -R 777 storage bootstrap/cache database \
    && chown -R www-data:www-data /var/www/html

# Start script
COPY docker-start.sh /docker-start.sh
RUN chmod +x /docker-start.sh

EXPOSE 80
CMD ["/docker-start.sh"]
