#!/bin/sh

ping -c 4 mysql-service
mysql -h mysql-service -uroot -pmonnetadmin monnet < /var/www/html/config/monnet.sql
exec ./vendor/bin/phpunit
