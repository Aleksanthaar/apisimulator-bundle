FROM php:7.3-cli-alpine

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-2.9.0 \
    && docker-php-ext-enable xdebug