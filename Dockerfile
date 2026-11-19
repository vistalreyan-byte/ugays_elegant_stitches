FROM php:8.2-apache

# Install MariaDB server and PHP MySQL extensions
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y mariadb-server && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    a2enmod rewrite && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Make entrypoint script executable
RUN chmod +x /var/www/html/docker-entrypoint.sh

# Fix file permissions for Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
