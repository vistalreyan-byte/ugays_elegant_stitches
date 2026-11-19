FROM php:8.2-apache

# Install MySQL extensions for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

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
