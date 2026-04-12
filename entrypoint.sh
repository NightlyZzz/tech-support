#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    cp .env.example .env
fi

if [ ! -d "vendor" ]; then
    composer install --no-interaction --no-progress --prefer-dist
fi

if [ "$#" -gt 0 ]; then
    exec "$@"
fi

exec php-fpm
