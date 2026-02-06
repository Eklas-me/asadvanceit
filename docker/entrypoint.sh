#!/bin/sh
set -e

echo "🚀 Starting ASAdvanceIT Application..."

# Ensure storage directories exist with correct permissions
echo "📂 Setting up storage directories..."
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Wait for MySQL to be ready (max 30 seconds)
echo "🔌 Waiting for MySQL database..."
max_retries=30
counter=0
until php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; do
    counter=$((counter + 1))
    if [ $counter -ge $max_retries ]; then
        echo "❌ Could not connect to database after $max_retries attempts"
        exit 1
    fi
    echo "   Waiting for database... (attempt $counter/$max_retries)"
    sleep 1
done
echo "✅ Database connected!"

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Cache configuration for production
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Application ready! Starting services..."

# Execute the main command (supervisord)
exec "$@"
