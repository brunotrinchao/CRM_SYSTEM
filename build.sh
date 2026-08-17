#!/bin/bash
set -e

echo "==> Checking PHP CLI..."
if ! command -v php &> /dev/null; then
    if [ ! -f ./php ]; then
        echo "--> Downloading static PHP CLI for Vercel build container..."
        # "common" set não traz ext-intl (exigida por filament/support) e a v8.3.0 é
        # baixa demais pro composer.lock atual (spatie/laravel-activitylog, symfony/*
        # travados em php >=8.4.1). Usa o set "bulk" (tem intl/pdo_mysql/gd/redis/zip)
        # na mesma versão do composer.lock.
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
# Vars sensíveis do dashboard Vercel (DB_*, CACHE_STORE, SESSION_DRIVER,
# QUEUE_CONNECTION etc) só são injetadas em runtime (execução da function),
# não durante o build — aqui elas chegam como string vazia, não ausentes.
# `artisan package:discover`/`filament:upgrade` (rodados pelo composer
# post-autoload-dump) fazem boot completo do framework e travam ao resolver
# um driver vazio ("Cache store [] is not defined."). Fallback só entra se a
# var estiver vazia/ausente — em runtime real (api/index.php) os valores
# reais do dashboard prevalecem normalmente.
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
