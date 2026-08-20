#!/bin/bash
set -e

echo "==> Checking PHP CLI..."
if ! command -v php &> /dev/null; then
    if [ ! -f ./php ]; then
        echo "--> Downloading static PHP CLI for Vercel build container..."
        curl -sSL -o php.tar.gz https://dl.static-php.dev/static-php-cli/bulk/php-8.4.23-cli-linux-x86_64.tar.gz
        tar -xzf php.tar.gz
        rm -f php.tar.gz
        chmod +x ./php
    fi
    PHP_BIN="./php"
else
    PHP_BIN="php"
fi

echo "==> Setting build-time fallback env..."
export CACHE_STORE="${CACHE_STORE:-array}"
export SESSION_DRIVER="${SESSION_DRIVER:-array}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_DATABASE="${DB_DATABASE:-:memory:}"

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

# Safety check: Ensure Filament CSS directory and theme file exist before Vite build
if [ ! -f ./vendor/filament/filament/resources/css/theme.css ]; then
    echo "--> Creating fallback Filament theme.css placeholder..."
    mkdir -p ./vendor/filament/filament/resources/css
    echo "@import 'tailwindcss' source(none);" > ./vendor/filament/filament/resources/css/theme.css
fi

echo "==> Building Frontend Assets with Vite..."
npm install
npm run build

echo "==> Publishing Filament Assets..."
$PHP_BIN artisan filament:assets || true

echo "==> Build Complete!"