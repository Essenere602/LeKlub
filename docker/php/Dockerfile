FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    unzip \
    zip \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
    intl \
    opcache \
    pdo \
    pdo_mysql \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend
