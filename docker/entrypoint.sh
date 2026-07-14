#!/bin/sh
set -e

# Laravel's sqlite connector requires the file to already exist -- it will
# not create it on its own (needed for Sanctum's personal_access_tokens).
DB_FILE="${DB_DATABASE:-database/database.sqlite}"
mkdir -p "$(dirname "$DB_FILE")"
touch "$DB_FILE"

if [ ! -L public/storage ]; then
  php artisan storage:link
fi

# --database=sqlite forces the migration *repository* (the bookkeeping of
# which migrations already ran) onto the sqlite connection too. Without it,
# the repository defaults to the app's default connection (mongodb) --
# since Mongo persists forever (Atlas) while the sqlite file lives on a
# Railway volume, the migration got marked "done" in Mongo once and every
# future "migrate --force" reported "Nothing to migrate" even against a
# brand new, empty sqlite file.
php artisan migrate --database=sqlite --force
php artisan db:seed --force
php artisan l5-swagger:generate

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
