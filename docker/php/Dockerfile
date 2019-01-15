FROM php:7.2-fpm-alpine3.7

RUN apk update && \
    apk add --no-cache \
        autoconf \
        pkgconfig \
        make \
        gcc \
        libc-dev \
        curl-dev \
        openssl-dev \
        libmcrypt-dev \
        libxml2-dev \
        zlib-dev \
        bzip2-dev \
        libgcc \
        libstdc++ \
        libx11 \
        glib \
        libxrender \
        libxext\
        libintl \
        libcrypto1.0 \
        libssl1.0

RUN docker-php-ext-install \
        bcmath \
        pcntl \
        sockets

RUN pecl install xdebug-2.6.1 \
        docker-php-ext-enable xdebug