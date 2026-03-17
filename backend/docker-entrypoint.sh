#!/bin/bash
set -e

# Генерируем APP_KEY, если он не задан
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    php artisan key:generate --force
fi

# Ждём MySQL (на случай, если healthcheck не сработал вовремя)
until php artisan db:show > /dev/null 2>&1; do
    echo "Ожидание MySQL..."
    sleep 2
done

# Миграции
php artisan migrate --force

# Передаём управление CMD (php-fpm)
exec "$@"
