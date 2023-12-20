FROM ojooss/webserver:8.2-latest
LABEL maintainer="ojooss"
ENV APP_ENV=prod

# apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# php
COPY docker/php.ini /usr/local/etc/php/conf.d/my-php.ini

# add security policy
ADD docker/ImageMagick-6-Policy.xml /etc/ImageMagick-6/policy.xml

# add and init application
COPY . /var/www/html
RUN composer install --no-dev --optimize-autoloader \
    && yarn encore production \
    && chown -R www-data:www-data /var/www/html

# Entrypoint
COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# start webserver
CMD ["apache2-foreground"]
