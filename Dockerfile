FROM ojooss/webserver:8.2-latest
LABEL maintainer="ojooss"

# apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# php
COPY docker/php.ini /usr/local/etc/php/conf.d/my-php.ini

# add security policy
ADD docker/ImageMagick-6-Policy.xml /etc/ImageMagick-6/policy.xml

# add and init application
COPY . /var/www/html
RUN composer install && touch /var/www/html/composer.done && \
    chown -R www-data:www-data /var/www/html

# start webserver
CMD ["apache2-foreground"]
