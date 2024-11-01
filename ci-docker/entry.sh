#!/bin/sh

ping -c 4 mysql
mysql -h mysql -uroot -pmonnetadmin monnet < /var/www/html/config/monnet.sql
exec ./vendor/bin/phpunit
