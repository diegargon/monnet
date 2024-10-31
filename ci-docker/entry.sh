#!/bin/sh
ls -al
mysql -uroot -pmonnetadmin monnet < /monnet.sql
exec ./vendor/bin/phpunit
