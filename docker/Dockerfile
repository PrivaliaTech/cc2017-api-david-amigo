# See https://hub.docker.com/_/php/
FROM php:7.1-apache

RUN apt-get update && \
    apt-get install zip unzip

RUN useradd -m -u 1000 -U david

RUN a2enmod rewrite

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && php -r "unlink('composer-setup.php');"

WORKDIR /var/www/html
