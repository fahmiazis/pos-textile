#!/bin/sh

echo "Starting Laravel application..."

# Wait for MySQL to be ready
echo "Waiting for database connection..."
MAX_TRIES=30
TRIES=0
until mysql -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" -p"$DB_PASSWORD" --ssl=0 -e "SELECT 1" > /dev/null 2>&1; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "Could not connect to database after $MAX_TRIES attempts. Exiting."
        exit 1
    fi
    echo "Database not ready, waiting 5 seconds... (attempt $TRIES/$MAX_TRIES)"
    sleep 5
done
echo "Database connected!"

# Run migrations
echo "Running migrations..."
php artisan migrate --force || echo "Migration failed, continuing..."

# Run seeders
echo "Running seeders..."
php artisan db:seed --force || echo "Seeder failed, continuing..."

# Create storage symlink
php artisan storage:link || true

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf