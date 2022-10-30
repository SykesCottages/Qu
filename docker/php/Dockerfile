FROM php:7.3-fpm

RUN echo 'Acquire::http::Pipeline-Depth "0";' > /etc/apt/apt.conf.d/00no-pipeline
ENV TZ=Europe/London
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && \
    apt-get install -y \
       software-properties-common \
       git \
       zip \
       libxml2-dev \
       libcurl4-gnutls-dev \
       libldb-dev \
       libonig-dev \
       libpng-dev \
       libzip-dev \
       libmagickwand-dev \
       libmemcached-dev

RUN docker-php-ext-install mysqli xml curl mbstring pdo_mysql gd zip soap bcmath sockets

COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

COPY docker/php/config/php.ini /usr/local/etc/php