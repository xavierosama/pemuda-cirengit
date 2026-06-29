#!/bin/sh
set -e

echo "🚀 Starting Laravel app on Northflank..."

cd /var/www/html

# Generate APP_KEY jika belum ada
if [ -z "$APP_KEY" ]; then
    echo "⚠️  APP_KEY not set, generating..."
    php artisan key:generate --force
fi

# Optimize Laravel untuk production
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run database migrations
echo "🗄️  Running migrations..."
php artisan migrate --force --no-interaction

echo "✅ Bootstrap complete. Starting services..."

exec "$@"
