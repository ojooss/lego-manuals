FROM ojooss/webserver:8.2-latest as app

ENV APP_ENV=prod

# add sources and prepare for production
COPY . /var/www/html
RUN yarn install \
 && yarn encore prod \
 && composer install --optimize-autoloader --no-dev  \
 && php bin/console ca:cl \
 && chown -R www-data:www-data /var/www/html


FROM ojooss/webserver:8.2-latest
LABEL maintainer="ojooss"
ENV APP_ENV=prod

# apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# php
COPY docker/php.ini /usr/local/etc/php/conf.d/my-php.ini

# add security policy
ADD docker/ImageMagick-6-Policy.xml /etc/ImageMagick-6/policy.xml

# add application
COPY --from=app  /var/www/html/ /var/www/html

# Volumes
VOLUME /var/www/html/public/data

# Entrypoint
COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# start webserver
CMD ["apache2-foreground"]
