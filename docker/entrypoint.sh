#!/bin/sh
set -eu

PORT="${PORT:-10000}"
export PORT

echo "Waiting for PostgreSQL..."
attempt=1
max_attempts=30

until php artisan db:show --database=pgsql >/dev/null 2>&1; do
    if [ "$attempt" -ge "$max_attempts" ]; then
        echo "PostgreSQL is unavailable after $max_attempts attempts; startup aborted." >&2
        exit 1
    fi

    echo "PostgreSQL is not ready (attempt $attempt/$max_attempts); retrying in 2 seconds." >&2
    attempt=$((attempt + 1))
    sleep 2
done

echo "PostgreSQL is ready; applying pending migrations."
if ! php artisan migrate --database=pgsql --force --no-interaction; then
    echo "Database migrations failed; refusing to start Nginx and PHP-FPM." >&2
    exit 1
fi

envsubst '${PORT}' \
    < /etc/nginx/templates/default.conf.template \
    > /etc/nginx/http.d/default.conf

exec /usr/bin/supervisord -c /etc/supervisord.conf
