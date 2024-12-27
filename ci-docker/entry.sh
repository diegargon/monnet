#!/bin/sh

apache2-foreground &
sleep 2
# TODO No se puede acceder por nombre por que estan en redes diferentes ¿que pasa si cambia la ip?
ping -c 1 mysql-service
ping -c 1 172.18.0.2

if ! mysql -h 172.18.0.2 -uroot -pmonnetadmin --verbose monnet < /var/www/html/config/monnet.sql; then
    echo "Error al ejecutar el script SQL. Asegúrate de que MySQL esté disponible."
else
    echo "Base de datos subida con exito"
fi

echo "Ejecutando phpunit"
exec ./vendor/bin/phpunit
echo "Fin"