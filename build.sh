#!/bin/bash
set -e

echo "==> Checking PHP CLI..."
if ! command -v php &> /dev/null; then
    if [ ! -f ./php ]; then
        echo "--> Downloading static PHP CLI for Vercel build container..."
        curl -sSL -o php.tar.gz https://dl.static-php.dev/static-php-cli/common/php-8.3.0-cli-linux-x86_64.tar.gz
        tar -xzf php.tar.gz
        rm -f php.tar.gz
        chmod +x ./php
    fi
    PHP_BIN="./php"
else
    PHP_BIN="php"
fi

echo "==> Preparing Composer..."
if ! command -v composer &> /dev/null; then
    if [ ! -f composer.phar ]; then
        echo "--> Downloading composer.phar..."
        curl -sS https://getcomposer.org/installer | $PHP_BIN
    fi
    COMPOSER="$PHP_BIN composer.phar"
else
    COMPOSER="composer"
fi

echo "==> Running Composer Install..."
$COMPOSER install --no-dev --prefer-dist --optimize-autoloader

echo "==> Building Frontend Assets with Vite..."
npm install
npm run build

echo "==> Publishing Filament Assets..."
$PHP_BIN artisan filament:assets || true

echo "==> Build Complete!"
