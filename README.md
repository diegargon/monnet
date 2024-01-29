# WARNING: Changing things that going to make backguard incompatible
# I disable the ssh stuff for redo, ping stats still works
.

# MonNet (NOT READY FOR USE)
Install LAMP

mysql -u root -p
mysql> CREATE DATABASE monnet;
mysql> CREATE USER 'monnet'@'localhost' IDENTIFIED BY 'password';
mysql> GRANT ALL PRIVILEGES ON monnet.* TO 'monnet'@'localhost'

/var/www/html# rm -rf *
/var/www/html# git clone https://github.com/diegargon/monnet .

Instalamos phpseclib con composer
/var/www/html# composer require phpseclib/phpseclib:~3.0


rename config/example.inc.php config.inc.php and cofig

## Settting Database
/var/www/html# mysql monnet < config/monnet.sql

## Setting crontab
$ nano /etc/crontab
*/3 * * * * root /usr/bin/php /var/www/html/monnet-cli.php

## CERTS (deprecated)

$ mkdir /var/certs && cd /var/certs 
$ ssh-keygen -m PEM -t rsa -b 4096

(without password)

add /var/certs/id_rsa to cfg[‘certs’] 

## Server to monitor (deprectated)

$ adduser monnet
$ usermod -aG sudo monnet
$ usermod -aG adm monnet

Add to /etc/sudoers
monnet ALL=(ALL) NOPASSWD: /sbin/poweroff, /sbin/reboot, /sbin/shutdown

From monnet server to server to monitor
$ ssh-copy-id -i /var/certs/id_rsa.pub monnet@192.168.2.61