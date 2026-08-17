#!/bin/bash
set -e

echo "==> Building Frontend Assets with Vite..."
npm install
npm run build

if command -v php &> /dev/null; then
    echo "==> Preparing Composer..."
    if ! command -v composer &> /dev/null; then
        if [ ! -f composer.phar ]; then
            echo "--> Composer CLI not found in PATH. Downloading composer.phar..."
            curl -sS https://getcomposer.org/installer | php || true
        fi
        if [ -f composer.phar ]; then
            COMPOSER="php composer.phar"
        fi
    else
        COMPOSER="composer"
    fi

    if [ -n "$COMPOSER" ]; then
        echo "==> Running Composer Install..."
        $COMPOSER install --no-dev --prefer-dist --optimize-autoloader || true
    fi

    echo "==> Publishing Filament Assets..."
    php artisan filament:assets || true
else
    echo "--> PHP CLI is not installed in the Node build environment. vercel-php will process PHP dependencies during lambda initialization."
fi

echo "==> Build Complete!"
