#!/bin/sh

echo "Initializing Railway Laravel Environment..."

# Ensure APP_KEY exists to prevent MissingAppKeyException
if [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY environment variable is empty. Generating key for session..."
    export APP_KEY=$(php artisan key:generate --show)
    echo "Generated APP_KEY: $APP_KEY"
fi

# Ensure required storage and cache directories exist and are writable
mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Create storage symlink
php artisan storage:link --no-interaction

# Execute database migrations
php artisan migrate --force

# Check if database has initial records using Laravel 11 autoloader
php -r '
  require "vendor/autoload.php";
  $app = require_once "bootstrap/app.php";
  $kernel = $app->make("Illuminate\Contracts\Console\Kernel");
  $kernel->bootstrap();
  try {
      if (\App\Models\User::count() === 0) {
          exit(1);
      }
      exit(0);
  } catch (\Throwable $e) {
      exit(1);
  }
'
STATUS=$?

if [ $STATUS -eq 1 ]; then
    echo "Database is unseeded. Running Waypoint automated setup..."
    php artisan waypoint:setup
    echo "Initial setup completed successfully."
else
    echo "Database already seeded. Skipping initial setup sequence."
fi

# Clear old caches and generate fresh production caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Laravel production web server
echo "Starting application web server on port $PORT..."
php artisan serve --host 0.0.0.0 --port $PORT
