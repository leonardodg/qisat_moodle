FROM php:7.3-apache

ARG UID=root
ARG GID=root
ARG USER

COPY ./docker/apache/ /etc/apache2/sites-available
COPY --chown=www-data:www-data ./docker/ssl /var/imported/ssl
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

RUN apt-get update  && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev apt-utils pngquant libpng-dev curl libssl-dev libmcrypt-dev libicu-dev libxml2-dev git libxslt-dev libzip-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    &&  /usr/local/bin/docker-php-ext-install zip bcmath gd xml xmlrpc dom session intl mysqli pdo_mysql mbstring soap opcache xsl pdo pdo_mysql \
    && a2enmod rewrite && a2enmod headers && a2enmod ssl && a2ensite moodle && a2ensite moodle-ssl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

CMD ["apachectl", "-D", "FOREGROUND"]

VOLUME /var/www/html/moodle
VOLUME /var/www/moodledata
RUN chown -R ${UID}:${GID} /var/www/html/moodle && chown -R ${UID}:${GID} /var/www/moodledata

EXPOSE 587