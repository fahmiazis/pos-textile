#!/bin/sh
set -e

echo "Starting Laravel application..."

# Wait for MySQL to be ready
echo "Waiting for database connection..."
until mysql -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" -p"$DB_PASSWORD" --ssl=0 -e "SELECT 1" > /dev/null 2>&1; do
    echo "Database not ready, waiting 3 seconds..."
    sleep 3
done
echo "Database connected!"

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Run seeders
echo "Running seeders..."
php artisan db:seed --force

# Cache config & routes for production
echo "Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Clear old compiled files
php artisan optimize

# Create storage symlink
php artisan storage:link || true

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf