#!/bin/bash
set -e
cd /app
# Ensure SQLite file exists
touch database/database.sqlite
# Run migrations + seed (idempotent)
php artisan migrate --force --seed 2>&1 || true
# Clear caches
php artisan config:clear 2>&1 || true
# Hand off to FrankenPHP
exec /start-container.sh
