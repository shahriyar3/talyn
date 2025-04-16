FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

RUN pecl install redis && docker-php-ext-enable redis

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN docker-php-ext-install opcache
COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY --chown=www-data:www-data . /var/www

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
