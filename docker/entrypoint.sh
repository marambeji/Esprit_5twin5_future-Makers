#!/bin/sh
set -eu

PORT="${PORT:-10000}"
export PORT

echo "Checking PostgreSQL connection..."

attempt=1
max_attempts=30

while true; do
    if php -r '
        try {
            require "vendor/autoload.php";
            $app = require "bootstrap/app.php";
            $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

            Illuminate\Support\Facades\DB::connection("pgsql")->getPdo();

            echo "DB_CONNECTION_OK\n";
            exit(0);
        } catch (Throwable $e) {
            echo "DB_CONNECTION_FAILED\n";
            echo "Exception class: " . get_class($e) . "\n";
            echo "Error code: " . $e->getCode() . "\n";
            exit(1);
        }
    '; then
        echo "PostgreSQL is ready."
        break
    fi

    echo "PostgreSQL connection check failed (attempt $attempt/$max_attempts)." >&2

    if [ "$attempt" -ge "$max_attempts" ]; then
        echo "PostgreSQL is unavailable after $max_attempts attempts; startup aborted." >&2
        exit 1
    fi

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
