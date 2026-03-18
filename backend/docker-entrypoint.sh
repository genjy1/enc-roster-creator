#!/bin/bash
set -e

# Генерируем APP_KEY, если он не задан
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    php artisan key:generate --force
fi

# Ждём MySQL через PDO (db:show требует intl-расширения и даёт ложный exit code 1)
until php -r "
  new PDO(
    'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
    '${DB_USERNAME}',
    '${DB_PASSWORD}'
  );
" 2>/dev/null; do
    echo "Ожидание MySQL..."
    sleep 2
done

# Миграции
php artisan migrate --force

# Laravel-кеш (только в production — ускоряет запросы)
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Передаём управление CMD (php-fpm)
exec "$@"
