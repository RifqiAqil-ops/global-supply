#!/bin/sh

# Ensure storage folder is linked
php artisan storage:link --no-interaction

# Run migrations (idempotent, safe to run on boot)
php artisan migrate --force

# Check if seeding is needed by checking if any users exist in the database
php -r '
  require "bootstrap/autoload.php";
  $app = require_once "bootstrap/app.php";
  $app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();
  if (\App\Models\User::count() === 0) {
      exit(1);
  }
  exit(0);
'
STATUS=$?

if [ $STATUS -eq 1 ]; then
    echo "Database is empty. Seeding initial records..."
    php artisan db:seed --force
    echo "Syncing country listings from API..."
    php artisan gscrip:sync-countries
    echo "Seeding global port mappings..."
    php artisan db:seed --class=WorldPortSeeder
    echo "Recalculating score indexes..."
    php artisan gscrip:recalculate-risk
    echo "Seeding completed successfully."
else
    echo "Database already seeded. Skipping initialization block."
fi

# Run caching and optimization commands
php artisan optimize

# Start application server
php artisan serve --host 0.0.0.0 --port $PORT
