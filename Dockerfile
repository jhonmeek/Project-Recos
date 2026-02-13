FROM composer:2 as composer
FROM php:8.2-fpm

# Installez les dépendances système (inclut git, zip, unzip pour Composer)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip exif pcntl bcmath gd xml intl

# Copiez le code
WORKDIR /app
COPY . .

# Installez Composer et dépendances (ajoutez --prefer-dist pour prioriser dist, et --ignore-platform-reqs pour bypasser checks)
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --ignore-platform-reqs

# Générez la clé et permissions
RUN php artisan key:generate
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Exposez le port
EXPOSE $PORT

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=$PORT"]
