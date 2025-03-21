#!/bin/bash

# Variables de conexión a MySQL
DB_HOST="mysql_container"
DB_USER="root"
DB_PASS="monnetadmin"
DB_NAME="monnet"
SQL_FILE="/var/www/html/config/monnet.sql"
ANSIBLE_SCRIPT="/opt/monnet-core/monnet_gateway/venv/bin/python3 /opt/monnet-core/monnet_gateway/mgateway.py"
CRON_LINE_1="*/5 * * * * root php /var/www/html/monnet-cli.php"
CRON_LINE_2="*/15 * * * * root php /var/www/html/monnet-discovery.php"
CRONTAB_FILE="/etc/crontab"

echo "V.28"

pwd
ls -al /opt/monnet-core
cd /opt/monnet-core
git fetch --verbose

# Configurar trabajos cron
echo "Configurando trabajos cron..."
if ! grep -Fxq "$CRON_LINE_1" "$CRONTAB_FILE"; then
  echo "$CRON_LINE_1" >> "$CRONTAB_FILE"
  echo "Añadido: $CRON_LINE_1"
else
  echo "La línea ya existe: $CRON_LINE_1"
fi

if ! grep -Fxq "$CRON_LINE_2" "$CRONTAB_FILE"; then
  echo "$CRON_LINE_2" >> "$CRONTAB_FILE"
  echo "Añadido: $CRON_LINE_2"
else
  echo "La línea ya existe: $CRON_LINE_2"
fi

cat /etc/crontab

service cron start
service cron status

mkdir -p "/etc/ansible"

cat > /etc/ansible/ansible.cfg <<EOF
[defaults]
stdout_callback=json
host_key_checking = False
EOF

cat /etc/ansible/ansible.cfg

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

echo "Conmfiguando mgateway..."
chmod +x /opt/monnet-core/monnet_gateway/install.bash
/opt/monnet-core/monnet_gateway/install.bash
echo "Iniciando el servicio mgateway..."
$ANSIBLE_SCRIPT &
sleep 3
if ps aux | grep -v grep | grep -q "mgateway.py"; then
    echo "El script está en ejecución."
else
    echo "El script NO está en ejecución."
    exit 1
fi

# Mantener el contenedor ejecutándose
echo "Inicialización completa. Contenedor listo."
exec "$@"
