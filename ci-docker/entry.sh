#!/bin/sh

mysql_start
ping -c 1 192.168.1.1
ping -c 4 mysql-service
mysql -h mysql-service -uroot -pmonnetadmin monnet < /var/www/html/config/monnet.sql
mysql -h 127.0.0.1 -uroot -pmonnetadmin monnet < /var/www/html/config/monnet.sql
exec ./vendor/bin/phpunit
