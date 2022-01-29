FROM php:8.1-fpm-alpine3.15

WORKDIR /app

RUN apk add --no-cache --upgrade \
    busybox-extras \
    postgresql-dev \
    libzip-dev \
    $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-install -j$(nproc) \
    pgsql \
    pdo_pgsql \
    bcmath \
    zip \
    opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
