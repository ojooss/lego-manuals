#!/usr/bin/env bash

# goto working dir
cd /var/www/html/

# update database schema
php bin/console doctrine:migrations:migrate --no-interaction

# start webserver
apache2-foreground
