FROM php:8.0-fpm

RUN apt-get update && \
    apt-get install -y \
       libzip-dev

RUN docker-php-ext-install sockets zip

COPY --from=composer:1 /usr/bin/composer /usr/bin/composer
