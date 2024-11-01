#!/bin/sh

apache2-foreground &
sleep 2
# TODO cant access to mysql-service by name only ip
ping -c 2 mysql-service

if ! mysql -h 172.18.0.1 -uroot -pmonnetadmin monnet < /var/www/html/config/monnet.sql; then
    echo "Error al ejecutar el script SQL. Asegúrate de que MySQL esté disponible."

exec ./vendor/bin/phpunit
