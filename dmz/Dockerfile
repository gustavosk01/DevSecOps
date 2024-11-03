FROM php:8.2-apache

COPY ./apache-config /etc/apache2
COPY ./certificates /etc/ssl
COPY ./php.ini $PHP_INI_DIR/php.ini

ENV APACHE_DOCUMENT_ROOT /var/www/html/front-end

RUN sed -ri -e 's!/var/www!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf && \
    apt-get update && \
    apt-get install -y libfreetype6-dev libjpeg-dev libpng-dev && \
    apt-get clean && \
    docker-php-ext-install mysqli exif gd && \
    a2enmod rewrite headers ssl && \
    a2ensite default-ssl && \
    chmod -R 750 /var/www/html && \
    chown -R www-data:www-data /var/www/html && \
    chown www-data:www-data /etc/ssl/private/mintbank.key && \
    chmod 400 /etc/ssl/private/mintbank.key && \
    chown www-data:www-data /etc/ssl/certs/mintbank.crt && \
    chmod 400 /etc/ssl/certs/mintbank.crt && \
    service apache2 restart
