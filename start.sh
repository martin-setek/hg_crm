#!/bin/bash
cd /app

# Storage writable
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

# SQLite
mkdir -p database
touch database/database.sqlite
chmod 666 database/database.sqlite

# Migrate + seed (safe)
php artisan migrate --force --seed --no-interaction 2>&1 || echo "migrate failed, continuing"

# Hand off
exec /start-container.sh
