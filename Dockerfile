FROM php:8.2-cli

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

WORKDIR /app

COPY . .

RUN composer install --optimize-autoloader --no-interaction

CMD php -S 0.0.0.0:$PORT -t public