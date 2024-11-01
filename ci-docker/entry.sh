#!/bin/sh

ping -c 2 mysql-service
mysql -h 172.18.0.1 -uroot -pmonnetadmin monnet < /var/www/html/config/monnet.sql
mysql -h 172.18.0.2 -uroot -pmonnetadmin monnet < /var/www/html/config/monnet.sql
mysql -h 127.0.0.1 -uroot -pmonnetadmin monnet < /var/www/html/config/monnet.sql
exec ./vendor/bin/phpunit
