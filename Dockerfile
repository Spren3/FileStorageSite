FROM php:8.3.8-apache

COPY my-apache-conf /etc/apache2/conf-enabled/
RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

COPY ./public_html /var/www/html

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

USER www-data