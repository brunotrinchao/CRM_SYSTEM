#!/bin/bash
set -e

echo "==> Verificando ambiente PHP..."
if command -v php &> /dev/null; then
    PHP_BIN="php"
    echo "--> Usando PHP do ambiente da Vercel: $(php -v | head -n 1)"
else
    echo "--> PHP não encontrado no PATH global. Verificando binário local..."
    if [ ! -f ./php ]; then
        echo "❌ Erro: Nenhum executável do PHP foi encontrado para o build."
        exit 1
    fi
    PHP_BIN="./php"
fi

echo "==> Configurando variáveis de ambiente temporárias para o build..."
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