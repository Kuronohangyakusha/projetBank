#!/bin/bash

# Wait for database to be ready (with timeout)
echo "Waiting for database to be ready..."
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_USERNAME: $DB_USERNAME"
if [ -n "$DB_PASSWORD" ]; then
  export PGPASSWORD="$DB_PASSWORD"
fi
timeout=30
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

# Run database migrations (skip if database is not ready)
echo "Running database migrations..."
if php artisan migrate --force 2>/dev/null; then
    echo "Migrations completed successfully"
else
    echo "Database not ready for migrations, skipping..."
fi

# Seed database if SEED_DB is set to true (skip if database is not ready)
if [ "$SEED_DB" = "true" ]; then
    echo "Seeding database..."
    if php artisan db:seed --force 2>/dev/null; then
        echo "Database seeding completed successfully"
    else
        echo "Database not ready for seeding, skipping..."
    fi
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

echo "Starting Apache..."
# Apache will listen on port 80 by default, Render handles port mapping
exec "$@"