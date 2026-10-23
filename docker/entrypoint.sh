#!/bin/sh
set -eu

PORT="${PORT:-10000}"
export PORT

envsubst '${PORT}' \
    < /etc/nginx/templates/default.conf.template \
    > /etc/nginx/http.d/default.conf

exec /usr/bin/supervisord -c /etc/supervisord.conf
