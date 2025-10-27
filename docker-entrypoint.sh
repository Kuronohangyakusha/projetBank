#!/bin/bash

# Wait for database to be ready (with timeout)
echo "Waiting for database to be ready..."
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_USERNAME: $DB_USERNAME"
export PGPASSWORD="$DB_PASSWORD"
timeout=60
elapsed=0
while ! pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" 2>/dev/null; do
  if [ $elapsed -ge $timeout ]; then
    echo "Database connection timeout after ${timeout}s, continuing startup..."
    break
  fi
  echo "Database is unavailable - sleeping"
  sleep 2
  elapsed=$((elapsed + 2))
done

echo "Database is ready!"

# Generate application key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate --no-interaction
fi

# Cache config and routes for production optimization
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache

# Generate Swagger documentation
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database if SEED_DB is set to true
if [ "$SEED_DB" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

echo "Starting Apache..."
# Set Apache to listen on the PORT provided by Render (default 80)
export APACHE_RUN_PORT=${PORT:-80}
exec "$@"