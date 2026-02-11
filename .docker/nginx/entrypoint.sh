#!/bin/sh
set -e

CERT_DIR="/etc/nginx/certs"
CONF_DIR="/etc/nginx/conf.d"

if [ -f "$CERT_DIR/cert.pem" ] && [ -f "$CERT_DIR/key.pem" ]; then
    echo "TLS certificates found, enabling HTTPS"
    cp /etc/nginx/templates/default.ssl.conf "$CONF_DIR/default.conf"
else
    echo "No TLS certificates found, serving HTTP only (run 'make certs' to enable HTTPS)"
    cp /etc/nginx/templates/default.conf "$CONF_DIR/default.conf"
fi

exec nginx -g 'daemon off;'
