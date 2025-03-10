#!/bin/bash

# Exit on error
set -e

echo "Deploying Metana Job Application Pipeline..."

# Pull latest code
echo "Pulling latest code..."
git pull

# Install dependencies
echo "Installing dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear caches
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Initialize Google Sheet
echo "Initializing Google Sheet..."
php artisan sheet:init

# Restart queue worker
echo "Restarting queue worker..."
php artisan queue:restart

echo "Deployment completed successfully!"