#!/bin/sh

ping -c 4 mysql-docker
mysql -h mysql-docker -uroot -pmonnetadmin monnet < /var/www/html/config/monnet.sql
exec ./vendor/bin/phpunit
