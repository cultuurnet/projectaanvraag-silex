FROM php:7.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends git zip unzip libicu-dev libtidy-dev zlib1g-dev libpng-dev libzip-dev supervisor

RUN docker-php-ext-install mysqli pdo_mysql bcmath tidy sockets

RUN docker-php-ext-configure zip && docker-php-ext-install zip

RUN docker-php-ext-configure intl && docker-php-ext-install intl

RUN docker-php-ext-configure pcntl --enable-pcntl && docker-php-ext-install pcntl

RUN docker-php-ext-configure gd && docker-php-ext-install gd

RUN pecl install -o -f redis &&  rm -rf /tmp/pear && docker-php-ext-enable redis

RUN pecl install -o -f mongodb-1.9.2 &&  docker-php-ext-enable mongodb

RUN curl https://getcomposer.org/download/1.10.22/composer.phar -o /usr/local/bin/composer && chmod +x /usr/local/bin/composer

RUN echo "memory_limit=4096M" > $PHP_INI_DIR/conf.d/memory-limit.ini

RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY custom-apache.conf /etc/apache2/conf-available/custom-apache.conf
RUN a2enconf custom-apache

RUN echo "Include conf-available/custom-apache.conf" >> /etc/apache2/apache2.conf

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

