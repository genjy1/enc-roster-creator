FROM php:8.4-fpm-alpine

# ─── Системные зависимости ────────────────────────────────────────────────────
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    zip \
    unzip \
    autoconf \
    g++ \
    make

# ─── PHP-расширения ───────────────────────────────────────────────────────────
RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    bcmath \
    pcntl \
    mbstring

# Redis через PECL (требует autoconf + g++ + make)
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

# ─── Composer ─────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

# Сначала только манифест зависимостей — кэшируем слой
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Копируем остальное
COPY . .

# Финальный autoload и скрипты
RUN composer dump-autoload --optimize

# Права на storage и bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/var/www/backend/docker-entrypoint.sh"]
CMD ["php-fpm"]
