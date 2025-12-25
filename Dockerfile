# Multi-stage build: PHP-FPM app image and Nginx image

# Stage 1: install Composer dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --no-scripts

# Stage 2: PHP-FPM app
FROM php:8.4-fpm-bookworm AS app
WORKDIR /var/www/html

RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
		git unzip ca-certificates \
		libzip-dev libicu-dev libonig-dev libxml2-dev \
		libjpeg62-turbo-dev libpng-dev libfreetype6-dev \
		sqlite3 libsqlite3-dev; \
	rm -rf /var/lib/apt/lists/*; \
	docker-php-ext-configure gd --with-freetype --with-jpeg; \
	docker-php-ext-install -j"$(nproc)" \
		bcmath exif gd intl mbstring pdo pdo_sqlite xml zip

# Copy application source
COPY . /var/www/html
# Copy vendor from builder
COPY --from=vendor /app/vendor /var/www/html/vendor

# Entrypoint to finalize app on container start
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV APP_ENV=production \
	APP_DEBUG=false \
	PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

EXPOSE 9000
ENTRYPOINT ["bash","/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

# Stage 3: Nginx web
FROM nginx:stable-alpine AS nginx
WORKDIR /var/www/html
COPY --from=app /var/www/html /var/www/html
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
EXPOSE 80

