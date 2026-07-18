FROM composer:2 AS vendor
WORKDIR /app
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

FROM php:8.5-cli
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libzip-dev poppler-utils \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY backend/ ./
COPY --from=vendor /app/vendor ./vendor
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
