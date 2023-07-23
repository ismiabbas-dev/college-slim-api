#!/bin/bash

# Step 1: Check for vendor folder and remove it if it exists
if [ -d "vendor" ]; then
    echo "Removing existing vendor folder..."
    rm -rf vendor
fi

# Step 2: Run composer install
echo "Running composer install..."
composer install

# Step 3: Run PHP server
echo "Starting PHP server..."
php -S 127.0.0.1:8080 -t public public/index.php
