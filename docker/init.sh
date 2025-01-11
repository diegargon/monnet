#!/bin/bash

# Variables de conexión a MySQL
DB_HOST="mysql_container"
DB_USER="root"
DB_PASS="root_password"
DB_NAME="monnet"
SQL_FILE="/var/www/html/config/monnet.sql"

# Esperar a que MySQL esté listo
echo "Esperando a que MySQL esté listo..."
until mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "status" > /dev/null 2>&1; do
  sleep 3
done

echo "MySQL está listo."

# Verificar si la base de datos ya existe
echo "Verificando si la base de datos '$DB_NAME' ya existe..."
if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME" > /dev/null 2>&1; then
  echo "La base de datos no existe. Inicializando..."
  mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" < "$SQL_FILE"
  echo "Base de datos inicializada correctamente."
else
  echo "La base de datos ya existe. No se realizaron cambios."
fi

