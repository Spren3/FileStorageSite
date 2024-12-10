FROM php:8.3-apache

COPY my-apache-conf /etc/apache2/conf-enabled/
RUN a2enmod rewrite

RUN apt-get update && apt-get install -y lsof
VOLUME /var/www/html/instance
WORKDIR /var/www/html

COPY show_db_files.php /tmp/show_db_files.php
COPY show_db_dirs.php /tmp/show_db_dirs.php
COPY . /var/www/html/
COPY ./public_html /var/www/html

RUN chown -R www-data:www-data /var/www/html

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

USER www-data