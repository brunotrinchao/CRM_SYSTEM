#!/bin/bash
set -e

echo "==> Running Composer Install..."
composer install --no-dev --prefer-dist --optimize-autoloader

echo "==> Building Frontend Assets with Vite..."
npm install
npm run build

echo "==> Publishing Filament Assets..."
php artisan filament:assets || true

echo "==> Build Complete!"
