#!/bin/bash
set -e

echo "==> Checking PHP CLI..."
if ! command -v php &> /dev/null; then
    if [ ! -f ./php ]; then
        echo "--> Downloading static PHP CLI for Vercel build container..."
        # Atualizado para uma versão existente e estável do PHP 8.4 no static-php-cli
        curl -sSL -o php.tar.gz https://dl.static-php.dev/static-php-cli/bulk/php-8.4.8-cli-linux-x86_64.tar.gz
        
        if [ ! -f php.tar.gz ] || [ ! -s php.tar.gz ]; then
            echo "❌ Erro: Falha ao baixar o binário do PHP."
            exit 1
        fi

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

echo "==> Building Frontend Assets with Vite..."
npm install
npm run build

echo "==> Publishing Filament Assets..."
$PHP_BIN artisan filament:assets || true

echo "==> Build Complete!"