#!/usr/bin/env bash

# goto working dir
cd /var/www/html/

# update database schema
php bin/console doctrine:migrations:migrate --no-interaction

    # set permissions
if [ "$APP_ENV" = "dev" ]; then
    chown -R 1000:www-data /var/www/html
    chmod ug+w -R /var/www/html/var
else
    chown -R www-data:www-data /var/www/html
    chmod ug+w -R /var/www/html/var
fi

# start webserver
apache2-foreground
