#!/bin/bash

set -e

echo "==> Iniciando build Laravel + Filament 5..."

# ---------------------------------------------------------
# PHP CLI
# ---------------------------------------------------------

if command -v php >/dev/null 2>&1; then
    PHP_BIN="php"
else
    echo "==> PHP CLI não encontrado. Baixando PHP 8.3..."

    if [ ! -f ./php ]; then
        curl -sSL \
            -o php.tar.gz \
            https://dl.static-php.dev/static-php-cli/common/php-8.3.14-cli-linux-x86_64.tar.gz

        if [ ! -s php.tar.gz ]; then
            echo "ERRO: Não foi possível baixar o PHP."
            exit 1
        fi

        tar -xzf php.tar.gz
        rm -f php.tar.gz

        chmod +x ./php
    fi

    PHP_BIN="./php"
fi

echo "==> PHP utilizado no build:"
$PHP_BIN -v

# ---------------------------------------------------------
# Composer
# ---------------------------------------------------------

if command -v composer >/dev/null 2>&1; then
    COMPOSER="composer"
else
    echo "==> Composer não encontrado. Baixando composer.phar..."

    if [ ! -f composer.phar ]; then
        curl -sS https://getcomposer.org/installer | $PHP_BIN
    fi

    COMPOSER="$PHP_BIN composer.phar"
fi

echo "==> Composer:"
$COMPOSER --version

# ---------------------------------------------------------
# Variáveis temporárias do Laravel
# ---------------------------------------------------------

export APP_ENV="${APP_ENV:-production}"
export APP_KEY="${APP_KEY:-base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=}"

export CACHE_STORE="${CACHE_STORE:-array}"
export SESSION_DRIVER="${SESSION_DRIVER:-array}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_DATABASE="${DB_DATABASE:-/tmp/build.sqlite}"

touch "$DB_DATABASE"

# ---------------------------------------------------------
# Composer
# ---------------------------------------------------------

echo "==> Instalando dependências PHP..."

$COMPOSER install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

# ---------------------------------------------------------
# Frontend / Vite
# ---------------------------------------------------------

if [ -f package.json ]; then

    echo "==> Instalando dependências Node..."

    if [ -f package-lock.json ]; then
        npm ci
    else
        npm install
    fi

    echo "==> Compilando assets..."

    npm run build

else

    echo "==> package.json não encontrado."

fi

# ---------------------------------------------------------
# Filament
# ---------------------------------------------------------

echo "==> Publicando assets do Filament..."

$PHP_BIN artisan filament:assets

# ---------------------------------------------------------
# Laravel
# ---------------------------------------------------------

echo "==> Otimizando Laravel..."

$PHP_BIN artisan optimize

echo "==> Build concluído com sucesso!"