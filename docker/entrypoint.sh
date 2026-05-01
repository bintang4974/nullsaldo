#!/bin/bash

set -e

echo "🚀 Starting NullSaldo Application..."

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
until php -r '
$host = getenv("DB_HOST") ?: "mysql";
$port = getenv("DB_PORT") ?: "3306";
$database = getenv("DB_DATABASE") ?: "nullsaldo";
$user = getenv("DB_USERNAME") ?: "nullsaldo";
$password = getenv("DB_PASSWORD") ?: "password";
try {
    new PDO("mysql:host={$host};port={$port};dbname={$database}", $user, $password);
    exit(0);
} catch (Throwable $exception) {
    exit(1);
}
'; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
done
echo "✅ MySQL is ready!"

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating APP_KEY..."
    php /app/artisan key:generate --force
fi

# Run migrations
echo "🔄 Running database migrations..."
php /app/artisan migrate --force

# Run seeders (optional - comment out if not needed)
# echo "🌱 Running database seeders..."
# php /app/artisan db:seed

# Cache config and routes for better performance
echo "⚡ Caching configuration and routes..."
php /app/artisan config:cache
php /app/artisan route:cache
php /app/artisan view:cache

# Clear any old cache
php /app/artisan cache:clear

echo "✨ Application setup complete!"

# Start supervisor to manage PHP-FPM and Nginx
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
