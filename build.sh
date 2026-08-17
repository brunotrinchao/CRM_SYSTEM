#!/bin/bash

set -e

echo "==> Iniciando build Laravel + Filament 5..."

# ---------------------------------------------------------
# PHP
# ---------------------------------------------------------

if command -v php >/dev/null 2>&1; then
    PHP_BIN="php"
else
    echo "ERRO: PHP não encontrado no ambiente de build."
    exit 1
fi

echo "==> Versão do PHP:"
$PHP_BIN -v

# ---------------------------------------------------------
# Composer
# ---------------------------------------------------------

echo "==> Verificando Composer..."

if command -v composer >/dev/null 2>&1; then
    COMPOSER="composer"
else
    echo "ERRO: Composer não encontrado no ambiente de build."
    exit 1
fi

echo "==> Versão do Composer:"
$COMPOSER --version

# ---------------------------------------------------------
# Variáveis necessárias durante o build
# ---------------------------------------------------------

export APP_ENV="${APP_ENV:-production}"
export APP_KEY="${APP_KEY:-base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=}"

export CACHE_STORE="${CACHE_STORE:-array}"
export SESSION_DRIVER="${SESSION_DRIVER:-array}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

# Evita dependência de banco durante o build
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_DATABASE="${DB_DATABASE:-/tmp/build.sqlite}"

# Cria SQLite temporário caso algum provider precise acessar o banco
if [ "$DB_CONNECTION" = "sqlite" ]; then
    touch "$DB_DATABASE"
fi

# ---------------------------------------------------------
# Composer install
# ---------------------------------------------------------

echo "==> Instalando dependências PHP..."

$COMPOSER install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

# ---------------------------------------------------------
# Node / Vite
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

    echo "==> package.json não encontrado. Pulando build frontend."

fi

# ---------------------------------------------------------
# Filament
# ---------------------------------------------------------

echo "==> Publicando assets do Filament..."

$PHP_BIN artisan filament:assets

# ---------------------------------------------------------
# Otimização Laravel
# ---------------------------------------------------------

echo "==> Otimizando Laravel..."

$PHP_BIN artisan optimize

echo "==> Build concluído com sucesso!"