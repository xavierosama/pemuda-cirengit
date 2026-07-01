#!/bin/sh
set -e

echo "🚀 Starting Laravel app on Northflank..."

cd /var/www/html

# Generate APP_KEY jika belum ada
if [ -z "$APP_KEY" ]; then
    echo "⚠️  APP_KEY not set, generating..."
    php artisan key:generate --force
fi

# Link storage ke public
echo "🔗 Linking storage..."
php artisan storage:link --force 2>/dev/null || true

# Optimize Laravel untuk production
echo "⚡ Optimizing Laravel..."
php artisan optimize

# Run database migrations
echo "🗄️  Running migrations..."
php artisan migrate --force --no-interaction

echo "✅ Bootstrap complete. Starting services..."

exec "$@"
