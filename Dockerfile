FROM php:8.2-apache

# Instala dependências do sistema e extensões PHP necessárias, incluindo pdo_pgsql para Postgres
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libpng-dev libonig-dev libxml2-dev libicu-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql zip intl

# Ativa mod_rewrite do Apache
RUN a2enmod rewrite

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia código-fonte para o container
COPY . .

# Instala o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instala dependências Laravel
RUN composer install --no-dev --optimize-autoloader

# Ajusta permissões para pastas que precisam de escrita
RUN chown -R www-data:www-data storage bootstrap/cache

# Altera Apache para apontar para public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Gera chave da aplicação e cache de config/rotas (se .env já existir)
RUN php artisan key:generate \
    && php artisan config:cache \
    && php artisan route:cache

# Inicia o Apache
CMD ["apache2-foreground"]


#Teste de deploy no render
