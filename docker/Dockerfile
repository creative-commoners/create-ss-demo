FROM php:7.2-apache

# Do get cwp-demo bundle from robbie before running that and extract here.
COPY . /var/www/html/
COPY ./php.ini /usr/local/etc/php/conf.d/custom.ini
COPY ./workers.conf /etc/apache2/mods-available/mpm_prefork.conf

ENV PATH="/var/www/html/scripts:$PATH"
RUN chmod +x /var/www/html/scripts/*

RUN apt-get update
RUN apt-get install -y mysql-client git netcat \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev zlib1g-dev libicu-dev g++
RUN docker-php-ext-configure intl
RUN docker-php-ext-configure mysqli --with-mysqli=mysqlnd
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install -j$(nproc) intl mysqli zip pdo pdo_mysql gd

RUN curl -sS https://silverstripe.github.io/sspak/install | php -- /usr/local/bin
RUN curl -sSLo /usr/local/bin/gosu https://github.com/tianon/gosu/releases/download/1.10/gosu-amd64
RUN chmod +x /usr/local/bin/gosu

RUN echo "ServerName localhost" > /etc/apache2/conf-enabled/fqdn.conf && \
    a2enmod rewrite expires remoteip cgid

RUN chown -R www-data:www-data /var/www/html/public/assets

EXPOSE 80
WORKDIR /var/www/html
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
