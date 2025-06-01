FROM richarvey/nginx-php-fpm:latest

COPY . .

# Copia o script de inicialização
COPY 00-laravel-deploy.sh /start.sh
RUN chmod +x /start.sh

# Configurações de ambiente
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_SIDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Permite o composer como root
ENV COMPOSER_ALLOW_SUPERUSER 1

# Comando para iniciar o container
CMD ["/start.sh"]
