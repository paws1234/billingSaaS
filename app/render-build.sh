#!/usr/bin/env bash
# Render Build Script
set -o errexit

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "Installing additional packages..."
composer require barryvdh/laravel-dompdf aws/aws-sdk-php stripe/stripe-php

echo "Generating application key..."
php artisan key:generate --force --no-interaction

echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Seeding database (first time only)..."
php artisan db:seed --force --no-interaction || true

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache

echo "Build complete!"
