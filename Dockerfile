FROM php:7-apache

RUN apt-get update && apt-get install -y libcurl3-dev

RUN docker-php-ext-install pdo_mysql curl

RUN a2enmod rewrite

COPY . /var/www/html

RUN chown www-data:www-data -R /var/www/html/*

CMD php /var/www/html/core/docker_entrypoint.php && apache2-foreground