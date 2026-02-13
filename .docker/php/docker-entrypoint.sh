#!/bin/sh
set -e

mkdir -p var/cache var/log

if [ "$(id -u)" = '0' ]; then
    chown -R app:app var/

    if [ "$1" = 'php-fpm' ]; then
        exec "$@"
    fi

    if [ "$1" = 'supervisord' ]; then
        exec "$@"
    fi

    exec su-exec app "$@"
fi

exec "$@"
