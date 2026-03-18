#!/bin/bash
set -e
cd /app
# Writable storage
mkdir -p storage/framework/{sessions,views,cache/data} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache
# SQLite
touch database/database.sqlite
chmod 666 database/database.sqlite
# Migrate + seed
php artisan migrate --force --seed 2>&1 || true
php artisan config:clear 2>&1 || true
# Start FrankenPHP
exec /start-container.sh
