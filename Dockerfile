FROM php:8.0-apache
LABEL maintainer="ojooss"


# COMPOSER
COPY --from=composer /usr/bin/composer /usr/bin/composer


# linux packages
RUN apt-get update && \
    apt-get install -y git unzip gnupg && \
    apt-get clean


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


# php
COPY docker/php.ini /usr/local/etc/php/conf.d/my-php.ini


# imagemagick (+ghostscript) for pdf handling
#RUN apt-get update && \
#    apt-get install -y --no-install-recommends \
#                       ghostscript \
#                       imagemagick \
#                       libmagickwand-dev && \
#    apt-get clean && \
#    printf "\n" | pecl install imagick && \
#    docker-php-ext-enable imagick
# -> workaround: imagick not php-8 ready
#https://github.com/mlocati/docker-php-extension-installer/issues/223
RUN apt-get update ; \
	apt-get install -y --no-install-recommends \
		libmagickwand-6.q16-[0-9]+ \
		libmagickcore-6.q16-[0-9]+-extra$ \
		ghostscript \
	; \
	savedAptMark="$(apt-mark showmanual)"; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
		git \
		libmagickwand-dev \
	; \
	rm -rf /var/lib/apt/lists/* ; \
	git clone https://github.com/Imagick/imagick ; \
	cd imagick ; \
	sed -i "s/#define PHP_IMAGICK_VERSION .*/#define PHP_IMAGICK_VERSION \"git-master-$(git rev-parse --short HEAD)\"/" php_imagick.h ; \
	phpize ; \
	./configure ; \
	make ; \
	make install ; \
	docker-php-ext-enable imagick ; \
	cd .. ; \
	rm -r imagick ; \
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark > /dev/null; \
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false
# add security policy
ADD docker/ImageMagick-6-Policy.xml /etc/ImageMagick-6/policy.xml


# add and init application
COPY . /var/www/html
RUN composer install && touch /var/www/html/composer.done && \
    chown -R www-data:www-data /var/www/html


# start webserver
CMD ["apache2-foreground"]
