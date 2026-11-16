#!/bin/sh
set -eu

port="${PORT:-8080}"
case "$port" in
    ''|0|*[!0-9]*)
        echo "PORT must be a positive integer." >&2
        exit 1
        ;;
esac

chown www-data:www-data /var/www/html/web_perunggu/images/uploads

sed -i "s/Listen 8080/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:8080>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
