FROM php:7.2-cli-alpine

RUN apk update \
 && apk add ${PHPIZE_DEPS} fann-dev \
 && pecl install fann \
 && docker-php-ext-enable fann \
 && apk del ${PHPIZE_DEPS} \
 && rm -frv /var/cache/apk/*

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/bin --filename=composer \
    && rm -fv composer-setup.php \
    && composer global require --prefer-dist --optimize-autoloader hirak/prestissimo

WORKDIR /app
