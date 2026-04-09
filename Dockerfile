# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts
RUN composer require cloudinary-labs/cloudinary-laravel --no-dev --optimize-autoloader --no-interaction --prefer-dist

FROM php:8.2-fpm-alpine

RUN apk add --no-cache bash git libzip-dev oniguruma-dev icu-dev zlib-dev libpng libpng-dev libjpeg-turbo-dev freetype-dev curl postgresql-dev
RUN docker-php-ext-configure zip
RUN docker-php-ext-configure gd --with-jpeg --with-freetype
RUN docker-php-ext-install pdo_mysql pdo_pgsql bcmath intl opcache zip gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .

RUN composer dump-autoload --optimize
RUN php artisan package:discover --ansi
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/vendor

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public public/index.php"]