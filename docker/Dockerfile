FROM php:8.2-apache

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git \
    mariadb-client \
    && docker-php-ext-install mysqli

# Clonar el repositorio en /var/www/html
RUN git clone https://github.com/diegargon/monnet.git /var/www/html

# Copiar el archivo monnet.sql al directorio de inicialización de MySQL
RUN mkdir -p /docker-entrypoint-initdb.d && \
    cp /var/www/html/config/monnet.sql /docker-entrypoint-initdb.d/monnet.sql

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html

# Habilitar módulos de Apache
RUN a2enmod rewrite

RUN ls -al .

RUN ls -al /var/www/html

# Crear el archivo de configuración en /etc/monnet
RUN mkdir -p /etc/monnet && \
    echo "<?php" > /etc/monnet/config.inc.php && \
    echo "\$cfg_db['dbtype'] = 'mysqli';" >> /etc/monnet/config.inc.php && \
    echo "\$cfg_db['dbhost'] = 'mysql_container';" >> /etc/monnet/config.inc.php && \
    echo "\$cfg_db['dbname'] = 'monnet';" >> /etc/monnet/config.inc.php && \
    echo "\$cfg_db['dbuser'] = 'root';" >> /etc/monnet/config.inc.php && \
    echo "\$cfg_db['dbpassword'] = 'monnetadmin';" >> /etc/monnet/config.inc.php

# Copiar script de inicialización
COPY docker/init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

# Ejecutar el script al iniciar el contenedor
CMD ["sh", "-c", "/usr/local/bin/init.sh && apache2-foreground"]
