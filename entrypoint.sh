#!/usr/bin/env sh
set -e

FILE=/var/www/html/.env
if [ ! -f "$FILE" ]; then
    set > $FILE
fi

sed -i "s/'/\"/g" $FILE

if [ ! -d "vendor" ];
    then composer install;
fi;

php-fpm
