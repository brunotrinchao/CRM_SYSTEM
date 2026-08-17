#!/bin/bash

set -e

echo "==> Iniciando build Laravel + Filament 5..."

echo "==> Node:"
node --version

echo "==> NPM:"
npm --version

echo "==> Instalando dependências frontend..."

if [ -f package-lock.json ]; then
    npm ci
else
    npm install
fi

echo "==> Compilando Vite..."

npm run build

echo "==> Build frontend concluído."