FROM php:8.2-apache

# Instala dependências do sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libpng-dev libonig-dev libxml2-dev libicu-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql zip intl

# Ativa mod_rewrite do Apache
RUN a2enmod rewrite

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia o composer do container oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia o restante do código-fonte para o container
COPY . .

# Instala dependências Laravel
RUN composer install --no-dev --optimize-autoloader

# Ajusta permissões para pastas que precisam de escrita
RUN chown -R www-data:www-data storage bootstrap/cache

# Altera o documento root do Apache para a pasta `public/`
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Exponha a porta padrão
EXPOSE 80

# Comando padrão do container (será sobrescrito se preencher o campo `Docker Command`)
CMD ["apache2-foreground"]
