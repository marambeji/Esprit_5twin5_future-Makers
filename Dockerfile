# Build Vite assets from the committed npm lockfile without carrying Node into runtime.
FROM node:22-alpine AS assets
WORKDIR /var/www/html
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# Shared PHP runtime and extension layer for Composer and the final image.
FROM php:8.3-fpm-alpine AS php-base
RUN apk add --no-cache libcurl libpq sqlite-libs libxml2 oniguruma \
    && apk add --no-cache --virtual .php-build-deps $PHPIZE_DEPS curl-dev postgresql-dev sqlite-dev libxml2-dev oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" bcmath curl dom mbstring pdo_pgsql pdo_sqlite xml \
    && apk del .php-build-deps \
    && sed -i 's/^;clear_env = no/clear_env = no/' /usr/local/etc/php-fpm.d/www.conf

# Production dependencies are installed from composer.lock; no .env is included.
FROM php-base AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Runtime image: Nginx serves public/ and forwards PHP requests to FPM.
FROM php-base AS runtime
RUN apk add --no-cache nginx supervisor gettext-envsubst \
    && mkdir -p /run/nginx /var/log/supervisor /etc/nginx/templates \
    && rm -f /etc/nginx/http.d/default.conf

WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /var/www/html /var/www/html
COPY --from=assets --chown=www-data:www-data /var/www/html/public/build /var/www/html/public/build
COPY docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/nutritrace-entrypoint

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 10000
ENTRYPOINT ["/bin/sh", "/usr/local/bin/nutritrace-entrypoint"]
