#!/bin/bash

echo "Running deployment script..."

# Run migrations
php artisan migrate --force

# Cache config and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deployment script completed."
