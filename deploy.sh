#!/bin/bash
# Ploi deploy script for travel2gether.webprvw.xyz
set -e

cd {SITE_ROOT}

git pull origin {BRANCH}

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reload PHP-FPM (Ploi replaces {PHP_VERSION})
echo "" | sudo -S service php{PHP_VERSION}-fpm reload
