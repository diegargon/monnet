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

echo "Verificando si la base de datos '$DB_NAME' ya existe..."
DB_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SHOW DATABASES LIKE '$DB_NAME';" | grep "$DB_NAME" || true)

if [ -z "$DB_EXISTS" ]; then
  echo "La base de datos no existe. Inicializando..."
  if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" < "$SQL_FILE"; then
    echo "Base de datos inicializada correctamente."
  else
    echo "Error al inicializar la base de datos. Verifica el archivo SQL."
    exit 1
  fi
else
  echo "La base de datos ya existe. No se realizaron cambios."
fi