#!/usr/bin/env bash

echo "🧹 Ajustando permissões..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo "📦 Instalando dependências do Composer..."
composer install --no-dev --optimize-autoloader --working-dir=/var/www/html

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "❌ vendor/autoload.php não encontrado. Composer falhou!"
    exit 1
fi

echo "⚙️ Cacheando configuração..."
php artisan config:cache

echo "🧭 Cacheando rotas..."
php artisan route:cache

echo "🎨 Otimizando Filament..."
php artisan filament:optimize || true

echo "🛠️ Executando migrations..."
php artisan migrate --force || true

echo "🚀 Iniciando servidor..."
exec supervisord -n
