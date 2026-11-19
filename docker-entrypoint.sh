#!/bin/bash
set -e

PORT=""
sed -i "s/Listen 80/Listen \/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:\>/g" /etc/apache2/sites-available/000-default.conf

# If no external MySQL host is configured, start built-in MariaDB
if [ -z "$MYSQLHOST" ] && [ -z "$DB_HOST" ]; then
    echo "Starting internal MariaDB server..."
    mkdir -p /var/run/mysqld
    chown -R mysql:mysql /var/run/mysqld /var/lib/mysql
    chmod 777 /var/run/mysqld

    /usr/bin/mysqld_safe --skip-syslog &

    echo "Waiting for MariaDB to initialize..."
    for i in {1..30}; do
        if mariadb-admin ping --silent 2>/dev/null || mysqladmin ping --silent 2>/dev/null; then
            break
        fi
        sleep 1
    done

    # Ensure database exists and root can connect without password from localhost
    mariadb -u root -e "
        CREATE DATABASE IF NOT EXISTS tailoring_rental_db;
        ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('');
        GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
        FLUSH PRIVILEGES;
    " 2>/dev/null || true
fi

# Run database auto-migration if needed
php /var/www/html/auto_migrate.php || true

echo "Starting Apache on port \..."
exec apache2-foreground