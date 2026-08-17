FROM php:8.2-apache

# Install PDO MySQL and cURL extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files into Apache web root
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
