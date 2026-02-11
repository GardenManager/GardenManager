#!/bin/sh
set -e

mkdir -p var/cache var/log

if [ "$(id -u)" = '0' ]; then
    chown -R app:app var/

    if [ "$1" = 'php-fpm' ]; then
        exec "$@"
    fi

    # Let supervisord run as root — it drops privileges to the
    # user specified in each [program:*] section via the user= directive.
    # Running supervisord itself as app (via su-exec) causes EACCES
    # when it tries to open /dev/stdout for child process log dispatchers.
    if [ "$1" = 'supervisord' ]; then
        exec "$@"
    fi

    exec su-exec app "$@"
fi

exec "$@"
