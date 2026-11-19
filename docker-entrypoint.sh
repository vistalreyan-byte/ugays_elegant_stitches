#!/bin/bash
set -e

PORT=""
sed -i "s/Listen 80/Listen \/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:\>/g" /etc/apache2/sites-available/000-default.conf

# If no external MySQL host is configured, start built-in MariaDB
if [ -z "$MYSQLHOST" ] && [ -z "$DB_HOST" ]; then
    echo "Starting internal MariaDB server..."
    service mariadb start
fi

# Run database auto-migration if needed
php /var/www/html/auto_migrate.php || true

echo "Starting Apache on port \..."
exec apache2-foreground