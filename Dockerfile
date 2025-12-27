FROM ojooss/webserver:8.5-latest AS app
ENV APP_ENV=prod

# add sources and prepare for production
COPY . /var/www/html
RUN composer install --optimize-autoloader --no-dev --no-scripts --ignore-platform-req=ext-imagick
#RUN php bin/console cache:clear
RUN npm install
RUN npm run build
#RUN php bin/console assets:install public
RUN chown -R www-data:www-data /var/www/html


FROM ojooss/webserver:8.5-latest
LABEL maintainer="ojooss"
ENV APP_ENV=prod

# add imagemagick and ghostscript
# from: https://pecl.php.net/package/imagick
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    libmagickwand-dev libmagickcore-dev ghostscript \
    && apt-get clean \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# add chrome
RUN apt-get update && \
    apt-get install -y wget gnupg ca-certificates --no-install-recommends && \
    wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub > /etc/apt/keyrings/google-chrome.asc && \
    echo "deb [arch=amd64 signed-by=/etc/apt/keyrings/google-chrome.asc] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google-chrome.list && \
    apt-get update && \
    apt-get install -y google-chrome-stable --no-install-recommends && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*
ENV CHROME_BIN=/usr/bin/google-chrome-stable
ENV CHROME_PATH=/usr/lib/chromium

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
