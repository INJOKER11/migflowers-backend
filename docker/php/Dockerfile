FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
        postgresql-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        icu-dev \
        oniguruma-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        intl \
        mbstring \
        bcmath \
        opcache \
        pcntl \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} www && adduser -G www -g www -s /bin/sh -D -u ${UID} www

USER www

EXPOSE 9000

CMD ["php-fpm"]

FROM base AS prod

FROM base AS dev

USER root

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug-config.ini
COPY docker/php/opcache-dev.ini /usr/local/etc/php/conf.d/opcache-dev.ini

USER www
