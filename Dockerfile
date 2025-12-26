FROM ojooss/webserver:8.5-latest AS app
ENV APP_ENV=prod

# add sources and prepare for production
COPY . /var/www/html
RUN composer install --optimize-autoloader --no-dev --no-scripts
#RUN php bin/console cache:clear
RUN yarn install
RUN yarn encore prod
#RUN php bin/console assets:install public
RUN chown -R www-data:www-data /var/www/html


FROM ojooss/webserver:8.5-latest
LABEL maintainer="ojooss"
ENV APP_ENV=prod

# add chrome
RUN apt-get update && \
    apt-get install -y wget gnupg --no-install-recommends && \
    wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add - && \
    sh -c 'echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google-chrome.list' && \
    apt-get update && \
    apt-get install -y google-chrome-stable --no-install-recommends && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

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
