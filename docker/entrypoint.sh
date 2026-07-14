#!/bin/sh
set -e

if [ ! -L public/storage ]; then
  php artisan storage:link
fi

php artisan migrate --force
php artisan db:seed --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
