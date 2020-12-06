FROM php:7.4-apache


# COMPOSER
COPY --from=composer /usr/bin/composer /usr/bin/composer


# linux packages
RUN apt-get update && \
    apt-get install -y git zip gnupg && \
    apt-get clean


# yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -  && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list  && \
    apt update && apt install -y yarn


# PHP modules
RUN docker-php-ext-install pdo pdo_mysql mysqli \
# xdebug
 && pecl install xdebug && docker-php-ext-enable xdebug


# apache configuration
RUN a2enmod headers && \
    a2enmod rewrite && \
    a2enmod ssl && \
    a2enmod proxy && \
    a2enmod proxy_http
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf


# imagemagick for pdf handling
RUN apt-get update && \
    apt-get install -y imagemagick ghostscript \
                       libmagickwand-dev --no-install-recommends && \
    apt-get clean && \
    printf "\n" | pecl install imagick && \
    docker-php-ext-enable imagick
ADD docker/ImageMagick-6-Policy.xml /etc/ImageMagick-6/policy.xml


# add and init application
COPY . /var/www/html
RUN composer install && touch /var/www/html/composer.done
RUN chown -R www-data:www-data /var/www/html


# start webserver
CMD ["apache2-foreground"]
