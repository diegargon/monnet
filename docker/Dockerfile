FROM php:8.2-apache

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git \
    mariadb-client \
    && docker-php-ext-install mysqli

# Clonar el repositorio en /var/www/html
RUN git clone https://github.com/diegargon/monnet.git /var/www/html

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html

# Habilitar módulos de Apache
RUN a2enmod rewrite

# Copiar script de inicialización
COPY docker/init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

# Ejecutar el script al iniciar el contenedor
CMD ["sh", "-c", "/usr/local/bin/init.sh && apache2-foreground"]
