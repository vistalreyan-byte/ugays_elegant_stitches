#!/bin/bash
set -e

PORT=""
sed -i "s/Listen 80/Listen \/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:\>/g" /etc/apache2/sites-available/000-default.conf

# Run database auto-migration if database is available
php /var/www/html/auto_migrate.php || true

exec apache2-foreground