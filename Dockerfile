FROM php:8.3-apache

RUN docker-php-ext-install mysqli \
    && rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork setenvif \
    && apache2ctl -M 2>/dev/null | grep -q 'mpm_prefork_module' \
    && ! apache2ctl -M 2>/dev/null | grep -Eq 'mpm_(event|worker)_module' \
    && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf \
    && printf '%s\n' \
        '<Directory /var/www/html>' \
        '    Options -Indexes +FollowSymLinks' \
        '    AllowOverride None' \
        '    Require all granted' \
        '</Directory>' \
        '<FilesMatch "(^\.|\.sql$|\.md$)">' \
        '    Require all denied' \
        '</FilesMatch>' \
        'SetEnvIf X-Forwarded-Proto "^https$" HTTPS=on' \
        > /etc/apache2/conf-available/perunggu.conf \
    && a2enconf perunggu

WORKDIR /var/www/html

COPY backend_perunggu/ /var/www/html/backend_perunggu/
COPY web_perunggu/ /var/www/html/web_perunggu/
COPY index.php /var/www/html/index.php
COPY docker/entrypoint.sh /usr/local/bin/perunggu-entrypoint

RUN chmod +x /usr/local/bin/perunggu-entrypoint \
    && mkdir -p /var/www/html/web_perunggu/images/uploads \
    && chown -R www-data:www-data \
        /var/www/html/web_perunggu/images/uploads \
        /var/www/html/web_perunggu/music

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/perunggu-entrypoint"]
