#!/bin/bash

set -e

echo "==> Iniciando build Laravel + Filament 5..."

# ---------------------------------------------------------
# PHP CLI
# ---------------------------------------------------------

if command -v php >/dev/null 2>&1; then
    PHP_BIN="php"
else
    echo "==> PHP CLI não encontrado. Baixando PHP 8.4..."

    if [ ! -f ./php ]; then

        curl -fL \
            -o php.tar.gz \
            https://dl.static-php.dev/static-php-cli/common/php-8.4.1-cli-linux-x86_64.tar.gz

        if [ ! -s php.tar.gz ]; then
            echo "ERRO: Não foi possível baixar o PHP 8.4."
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

# Garante PHP >= 8.4.1
PHP_VERSION=$($PHP_BIN -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION.".".PHP_RELEASE_VERSION;')

echo "==> PHP detectado: $PHP_VERSION"

# ---------------------------------------------------------
# Composer
# ---------------------------------------------------------

if command -v composer >/dev/null 2>&1; then
    COMPOSER="composer"
else
    echo "==> Composer não encontrado. Baixando..."

    if [ ! -f composer.phar ]; then
        curl -sS https://getcomposer.org/installer | $PHP_BIN
    fi

    COMPOSER="$PHP_BIN composer.phar"
fi

echo "==> Composer:"
$COMPOSER --version

# ---------------------------------------------------------
# Ambiente de build
# ---------------------------------------------------------

export APP_ENV="production"
export APP_KEY="${APP_KEY:-base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=}"

export CACHE_STORE="array"
export SESSION_DRIVER="array"
export QUEUE_CONNECTION="sync"

export DB_CONNECTION="sqlite"
export DB_DATABASE="/tmp/build.sqlite"

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
# Frontend
# ---------------------------------------------------------

if [ -f package.json ]; then

    echo "==> Instalando dependências Node..."

    if [ -f package-lock.json ]; then
        npm ci
    else
        npm install
    fi

    echo "==> Compilando Vite..."

    npm run build

else

    echo "==> package.json não encontrado. Pulando frontend."

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