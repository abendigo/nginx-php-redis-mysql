FROM php:fpm-alpine

RUN docker-php-ext-install mysqli
RUN curl -sS https://getcomposer.org/installer | php

COPY composer.json ./
RUN php composer.phar install

CMD ["php-fpm"]

EXPOSE 9000
