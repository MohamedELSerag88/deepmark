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
mkdir -p storage/logs
touch storage/logs/laravel.log
chown -R www-data:www-data storage/logs || true
chmod -R ug+rwX storage/logs || true

# On development environments, relax permissions to avoid host volume ACL issues
if [ "${APP_ENV:-production}" != "production" ] || [ "${APP_DEBUG:-false}" = "true" ]; then
	chmod -R 777 storage bootstrap/cache database || true
fi

php artisan key:generate --force || true
php artisan storage:link || true
php artisan migrate --force || true
php artisan package:discover --ansi || true

# Cache only in production; otherwise keep things clear for development
if [ "${APP_ENV:-production}" = "production" ] || [ "${APP_DEBUG:-false}" = "false" ]; then
	php artisan config:clear || true
	php artisan config:cache || true
	php artisan route:cache || true
	php artisan view:cache || true
else
	php artisan config:clear || true
	php artisan route:clear || true
	php artisan view:clear || true
fi

exec "$@"


