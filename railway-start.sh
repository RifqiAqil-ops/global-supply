#!/bin/sh

echo "Initializing Railway Laravel Environment..."

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
    echo "Database is unseeded. Running initial seed sequence..."
    php artisan db:seed --force
    echo "Syncing country dataset..."
    php artisan gscrip:sync-countries
    echo "Seeding world ports dataset..."
    php artisan db:seed --class=WorldPortSeeder
    echo "Recalculating risk score indexes..."
    php artisan gscrip:recalculate-risk
    echo "Initial seeding completed successfully."
else
    echo "Database already seeded. Skipping initial seed sequence."
fi

# Clear old caches and generate fresh production caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Laravel production web server
echo "Starting application web server on port $PORT..."
php artisan serve --host 0.0.0.0 --port $PORT
