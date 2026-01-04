#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f ".env" ]; then
	cp -n .env.example .env || true
fi

mkdir -p database storage bootstrap/cache
touch database/database.sqlite
chown -R www-data:www-data storage bootstrap/cache database || true
chmod -R ug+rwX storage bootstrap/cache database || true

# Ensure Laravel cache directories exist
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views
chown -R www-data:www-data storage/framework || true
chmod -R ug+rwX storage/framework || true

php artisan key:generate --force || true
php artisan storage:link || true
php artisan migrate --force || true
php artisan config:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"


