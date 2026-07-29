#!/bin/sh
set -e

mkdir -p var/cache var/log

if [ "$(id -u)" = '0' ]; then
    # CIFS/NFS bind mounts don't support chown; ownership is forced by mount options
    chown -R app:app var/ 2>/dev/null || true

    if [ "$1" = 'php-fpm' ]; then
        exec "$@"
    fi

    if [ "$1" = 'supervisord' ]; then
        exec "$@"
    fi

    exec su-exec app "$@"
fi

exec "$@"
