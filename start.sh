#!/bin/bash

echo "Starting Laravel setup..."

# pastikan folder storage ada
mkdir -p storage/app/public/products
mkdir -p storage/app/public/variants
mkdir -p storage/logs

# fix permission
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# generate app key 
php artisan key:generate --force || true

# storage link
php artisan storage:link || true

# run migration
php artisan migrate --force 

# jalankan seeder
php artisan db:seed --force

# cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Laravel ready!"

# jalankan server
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}