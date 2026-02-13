FROM composer:2 as composer
FROM php:8.2-fpm

# Installez les dépendances système
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Copiez le code
WORKDIR /app
COPY . .

# Installez Composer et dépendances
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Générez la clé si needed (mais mieux en env var)
RUN php artisan key:generate

# Exposez le port (Render utilisera $PORT, mais configurez pour 9000 internement pour PHP-FPM)
EXPOSE 9000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=$PORT"]
