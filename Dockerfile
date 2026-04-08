# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx bash git libzip-dev oniguruma-dev icu-dev zlib-dev libpng libpng-dev libjpeg-turbo-dev freetype-dev curl
RUN docker-php-ext-configure zip
RUN docker-php-ext-configure gd --with-jpeg --with-freetype
RUN docker-php-ext-install pdo_mysql bcmath intl opcache zip gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .

RUN ls -la | head -20
RUN ls -la .env* || echo "No .env files found"
RUN if [ -f .env.example ]; then cp .env.example .env && echo "Copied .env.example to .env"; else echo ".env.example not found, creating empty .env"; touch .env; fi
RUN ls -la .env
RUN php artisan key:generate --ansi
RUN composer config autoload.psr-4 'App\\\\Modules\\\\' 'app/Modules/' --no-interaction
RUN composer dump-autoload --optimize
RUN php artisan package:discover --ansi
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/vendor

RUN mkdir -p /run/nginx
RUN cat > /etc/nginx/nginx.conf <<'EOF'
events { worker_connections 1024; }
http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;
    sendfile       on;
    keepalive_timeout  65;

    server {
        listen 8080;
        server_name _;
        root /var/www/html/public;
        index index.php index.html;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.ht {
            deny all;
        }
    }
}
EOF

EXPOSE 8080

CMD ["sh", "-c", "php-fpm && nginx -g 'daemon off;'"]
