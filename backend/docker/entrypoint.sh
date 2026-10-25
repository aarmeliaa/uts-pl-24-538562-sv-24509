#!/bin/sh
set -e

# Create or update the tables, then make sure the demo account exists.
# Both commands are safe to run on every start.
php artisan migrate --force
php artisan db:seed --force

# --no-reload keeps the container environment variables (DB_HOST, APP_KEY, ...)
# visible to the PHP server process.
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
