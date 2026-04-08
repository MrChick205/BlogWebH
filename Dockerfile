# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx bash git libzip-dev oniguruma-dev icu-dev zlib-dev libpng libpng-dev libjpeg-turbo-dev freetype-dev curl
RUN docker-php-ext-configure zip
RUN docker-php-ext-configure gd --with-jpeg --with-freetype
RUN docker-php-ext-install pdo_mysql bcmath intl opcache zip gd

WORKDIR /var/www/html
COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .

RUN cp .env.example .env \
    && php artisan key:generate --ansi \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

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
