#!/bin/sh

apache2-foreground &

# TODO No se puede acceder por nombre por que estan en redes diferentes ¿que pasa si cambia la ip?
ping -c 1 mysql-service

for i in {1..10}; do
  if mysql -h mysql-service -uroot -pmonnetadmin -e "SELECT 1;" >/dev/null 2>&1; then
    echo "MySQL is up and running!"
    break
  fi
  echo "Waiting for MySQL to start..."
  sleep 1
done
if ! mysql -h mysql-service -uroot -pmonnetadmin --verbose monnet < /var/www/html/config/monnet.sql; then
    echo "Error al ejecutar el script SQL. Asegúrate de que MySQL esté disponible."
else
    echo "Base de datos subida con exito"
fi

echo "Ejecutando phpunit"
exec ./vendor/bin/phpunit
echo "Fin"