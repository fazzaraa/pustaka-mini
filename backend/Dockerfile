FROM dunglas/frankenphp:1.4-php8.3-alpine

# Ekstensi PHP yang dibutuhkan Laravel + driver basis data
RUN install-php-extensions pdo_mysql zip opcache

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Salin file dependensi dulu supaya layer ini ter-cache selama composer.json tidak berubah
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Baru salin kode aplikasi
COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Layani HTTP biasa di port 8000 (tanpa HTTPS otomatis)
ENV SERVER_NAME=":8000"
EXPOSE 8000