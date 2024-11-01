#!/bin/sh

apache2-foreground &
sleep 2
# TODO No se puede acceder por nombre por que estan en redes diferentes ¿que pasa si cambia la ip?
ping -c 1 mysql-service

if ! mysql -h 172.18.0.1 -uroot -pmonnetadmin monnet < /var/www/html/config/monnet.sql; then
    echo "Error al ejecutar el script SQL. Asegúrate de que MySQL esté disponible."
fi

exec ./vendor/bin/phpunit
