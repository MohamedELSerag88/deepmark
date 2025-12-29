#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f ".env" ]; then
	cp -n .env.example .env || true
fi

if grep -q '^DB_CONNECTION=' .env; then
	sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
else
	echo 'DB_CONNECTION=sqlite' >> .env
fi

if grep -q '^DB_DATABASE=' .env; then
	sed -i 's|^DB_DATABASE=.*|DB_DATABASE=/var/www/html/database/database.sqlite|' .env
else
	echo 'DB_DATABASE=/var/www/html/database/database.sqlite' >> .env
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


