# MonNet (NOT READY FOR USE)
Install LAMP

mysql -u root -p
mysql> CREATE DATABASE monnet;
mysql> CREATE USER 'monnet'@'localhost' IDENTIFIED BY 'contraseña';
mysql> GRANT ALL PRIVILEGES ON monnet.* TO 'monnet'@'localhost'

/var/www/html# rm -rf *
/var/www/html# git clone https://github.com/diegargon/monnet .
(ojo hay un punto al final)
Instalamos phpseclib con composer
/var/www/html# composer require phpseclib/phpseclib:~3.0


rename config/example.inc.php config.inc.php and fill

/var/www/html# mysql monnet < config/monnet.sql

$ nano /etc/crontab
*/3 * * * * root /usr/bin/php /var/www/html/monnet-cli.php

# CERTS

$ mkdir /var/certs && cd /var/certs 
$ ssh-keygen -m PEM -t rsa -b 4096

(without password)

add /var/certs/id_rsa to cfg[‘certs’] 

# Server to monitor

$ adduser monnet
$ usermod -aG sudo monnet
$ usermod -aG adm monnet

Add to /etc/sudoers
monnet ALL=(ALL) NOPASSWD: /sbin/poweroff, /sbin/reboot, /sbin/shutdown

# Final
From monnet server to server to monitor
$ ssh-copy-id -i /var/certs/id_rsa.pub monnet@192.168.2.61