FROM node:14-alpine AS build
RUN apk add --no-cache libc6-compat
WORKDIR /app
COPY . .
RUN yarn set version berry
RUN yarn install --frozen-lockfile
RUN yarn run build

FROM php:7-apache AS final

# Install Required Extensions
RUN apt-get update && apt-get install -y libcurl3-dev libzip-dev zip
RUN docker-php-ext-install pdo_mysql curl zip
RUN a2enmod rewrite

# Copy Files
WORKDIR /var/www/html
COPY . .
RUN rm -rf assets/tailwind.*.css
COPY --from=build /app/assets/tailwind.index.css assets/

# Install dependencies
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN composer install

# Set up files
RUN chown www-data:www-data -R /var/www/html/*
RUN chown www-data:www-data -R /var/www/html/.*

# Set up permissions
USER www-data

CMD php /var/www/html/core/docker_entrypoint.php && apache2-foreground