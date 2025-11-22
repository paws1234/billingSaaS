FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip libonig-dev libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql zip

WORKDIR /var/www/html

CMD ["php-fpm"]
