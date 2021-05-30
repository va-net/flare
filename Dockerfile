FROM php:7-apache

# Install Required Extensions
RUN apt-get update && apt-get install -y libcurl3-dev
RUN docker-php-ext-install pdo_mysql curl
RUN a2enmod rewrite

# Copy Files
COPY . /var/www/html

# Set up files
RUN chown www-data:www-data -R /var/www/html/*

# Install dependencies
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
RUN composer install

CMD php /var/www/html/core/docker_entrypoint.php && apache2-foreground