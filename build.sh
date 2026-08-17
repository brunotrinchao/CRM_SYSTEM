#!/bin/bash
set -e

echo "==> Verificando/Baixando PHP CLI estático para o build..."
if ! command -v php &> /dev/null; then
    if [ ! -f ./php ]; then
        echo "--> Baixando PHP 8.3 CLI..."
        curl -sSL -o php.tar.gz https://dl.static-php.dev/static-php-cli/common/php-8.3.14-cli-linux-x86_64.tar.gz
        
        if [ ! -f php.tar.gz ] || [ ! -s php.tar.gz ]; then
            echo "❌ Erro: O download do PHP falhou."
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

echo "--> Versão do PHP em uso no build:"
$PHP_BIN -v

echo "==> Configurando variáveis de ambiente temporárias..."
export CACHE_STORE="${CACHE_STORE:-array}"
export SESSION_DRIVER="${SESSION_DRIVER:-array}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_DATABASE="${DB_DATABASE:-:memory:}"

echo "==> Preparando o Composer..."
if ! command -v composer &> /dev/null; then
    if [ ! -f composer.phar ]; then
        echo "--> Baixando composer.phar..."
        curl -sS https://getcomposer.org/installer | $PHP_BIN
    fi
    COMPOSER="$PHP_BIN composer.phar"
else
    COMPOSER="composer"
fi

echo "==> Instalando dependências do Composer (Production)..."
$COMPOSER install --no-dev --prefer-dist --optimize-autoloader

echo "==> Compilando assets do Frontend com Vite..."
if [ -f package.json ]; then
    npm install
    npm run build
fi

echo "==> Publicando assets do Filament..."
$PHP_BIN artisan filament:assets || true

echo "==> Build concluído com sucesso!"