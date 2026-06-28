# Build frontend assets
FROM node:20-alpine AS node-builder
WORKDIR /var/www/html
COPY package.json package-lock.json* ./
RUN npm ci
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# Install PHP vendor dependencies
FROM composer:2.7 AS composer
WORKDIR /app
COPY composer.json composer.lock ./
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Final application image
FROM php:8.2-fpm-alpine
RUN apk add --no-cache bash icu-dev oniguruma-dev libzip-dev zlib-dev git shadow $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis
RUN docker-php-ext-install intl pdo_mysql zip bcmath opcache

WORKDIR /var/www/html
COPY --from=composer /app/vendor ./vendor
COPY --from=node-builder /var/www/html/public/build ./public/build
COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stack

EXPOSE 9000
CMD ["php-fpm"]
