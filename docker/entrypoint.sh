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

rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf
a2enmod mpm_prefork

if ! apache2ctl -M >/tmp/apache-modules.txt 2>&1; then
    cat /tmp/apache-modules.txt >&2
    exit 1
fi

mpm_modules=$(awk '$1 ~ /^mpm_/ { print $1 }' /tmp/apache-modules.txt)
if [ "$mpm_modules" != "mpm_prefork_module" ]; then
    echo "Expected only mpm_prefork_module; found: ${mpm_modules:-none}" >&2
    cat /tmp/apache-modules.txt >&2
    exit 1
fi

rm -f /tmp/apache-modules.txt

exec apache2-foreground
