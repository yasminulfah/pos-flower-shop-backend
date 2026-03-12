#!/bin/bash

echo "Starting Laravel setup..."

# generate app key 
php artisan key:generate --force

# storage link
php artisan storage:link || true

# run migration
php artisan migrate --force || true

# cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Laravel ready!"

apache2-foreground