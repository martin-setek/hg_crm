#!/bin/bash
set -e
composer install --no-dev --optimize-autoloader --no-interaction
touch /var/data/database.sqlite
php artisan migrate --force --seed
