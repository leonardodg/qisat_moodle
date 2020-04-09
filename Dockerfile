FROM php:7.3-apache

# Install
RUN apt-get update \
    && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev \
        apt-utils pngquant libpng-dev curl libssl-dev \
        libmcrypt-dev libicu-dev libxml2-dev git libxslt-dev libzip-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    &&  /usr/local/bin/docker-php-ext-install zip bcmath gd xml xmlrpc dom session intl mysqli pdo_mysql mbstring soap opcache xsl pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite && a2enmod headers

# Install Composer PHP
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer