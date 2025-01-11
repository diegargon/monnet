#!/bin/bash

# Variables de conexión a MySQL
DB_HOST="mysql_container"
DB_USER="root"
DB_PASS="monnetadmin"
DB_NAME="monnet"
SQL_FILE="/var/www/html/config/monnet.sql"
CRONTAB_FILE="/etc/cron.d/monnet-jobs"
ANSIBLE_FILE="/etc/ansible/ansible.cfg"

echo "V6";

# Configurar trabajos cron
echo "Configurando trabajos cron..."
cat > "$CRONTAB_FILE" <<EOF
*/5 * * * * root /usr/bin/php /var/www/html/monnet-cli.php
*/15 * * * * root /usr/bin/php /var/www/html/monnet-discovery.php
EOF

chmod 0644 "$CRONTAB_FILE"
service cron restart

cat > "$ANSIBLE_FILE" <<EOF
[defaults]
stdout_callback=json
EOF

cat "$ANSIBLE_FILE"

systemctl start monnet-ansible

# Esperar a que MySQL esté listo
echo "Esperando a que MySQL esté listo..."
until mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "status" > /dev/null 2>&1; do
  sleep 3
done

echo "MySQL está listo."

# Verificar si la base de datos tiene tablas
echo "Verificando si la base de datos '$DB_NAME' tiene tablas..."
TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | wc -l)

if [ "$TABLE_COUNT" -le 1 ]; then
  echo "La base de datos '$DB_NAME' no contiene tablas. Volcando el archivo SQL..."
  if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE"; then
    echo "Archivo SQL volcado exitosamente en la base de datos '$DB_NAME'."
  else
    echo "Error al volcar el archivo SQL. Verifica el archivo y los permisos."
    exit 1
  fi
else
  echo "La base de datos '$DB_NAME' ya contiene tablas. No se realizaron cambios."
fi

# Mostrar las tablas de la base de datos
echo "Mostrando las tablas de la base de datos '$DB_NAME':"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "SHOW TABLES;"
