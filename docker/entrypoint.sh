#!/bin/sh
set -e

# Laravel's sqlite connector requires the file to already exist -- it will
# not create it on its own (needed for Sanctum's personal_access_tokens).
DB_FILE="${DB_DATABASE:-database/database.sqlite}"
echo "DEBUG: DB_FILE resuelto = [$DB_FILE]"
mkdir -p "$(dirname "$DB_FILE")"
touch "$DB_FILE"
echo "DEBUG: contenido de $(dirname "$DB_FILE"):"
ls -la "$(dirname "$DB_FILE")"
echo "DEBUG: tamano de $DB_FILE: $(wc -c < "$DB_FILE") bytes"

if [ ! -L public/storage ]; then
  php artisan storage:link
fi

php artisan migrate --force
php artisan db:seed --force
php artisan l5-swagger:generate

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
