FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libpng-dev libonig-dev libxml2-dev libicu-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl

RUN a2enmod rewrite

WORKDIR /var/www/html


COPY . .

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache


RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

#Teste do webhook do caprover + desencargo