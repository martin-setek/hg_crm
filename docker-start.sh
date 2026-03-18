#!/bin/bash
set -e
cd /var/www/html
touch database/database.sqlite
chmod 666 database/database.sqlite
php artisan migrate --force --seed --no-interaction 2>&1
php artisan config:clear 2>&1
apache2-foreground
