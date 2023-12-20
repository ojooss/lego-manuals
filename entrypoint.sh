#!/usr/bin/env bash

cd /var/www/html/

php bin/console doctrine:migrations:migrate --no-interaction

apache2-foreground
