###

# MonNet

MonNet is a hybrid of a homepage accessible from your web browser and a preview/summary of your network, featuring additional options.

<p align="center">
<img width="400" src="https://github.com/diegargon/monnet/blob/main/monnet.png?raw=true" width="100%">
</p>

## MonNet Status

MonNet is currently in an early version. A rudimentary method has been added for updating, but it is possible that issues may arise between versions. The compatibility is not guaranteed.

You can add bookmarks, networks, and other elements, but for most features, modification is not yet possible. (Except by directly accessing the database, obviously) 

The English language (EN) has not been added yet. /lang/*

# MonNet Install
Install LAMP + Composer

```
mysql -u root -p
mysql> CREATE DATABASE monnet;
mysql> CREATE USER 'monnet'@'localhost' IDENTIFIED BY 'password';
mysql> GRANT ALL PRIVILEGES ON monnet.* TO 'monnet'@'localhost'
```

```
/var/www/html# git clone https://github.com/diegargon/monnet .

/var/www/html# chown -R www-data:www-data *
```
Instalamos phpseclib con composer

```
/var/www/html# composer require phpseclib/phpseclib:~3.0
```

Copy  config/config.defaults.php  to config.inc.php and change whatever you want, you
can remove all unnchaged config. Don't rename or modify directly config.defaults.php

## Settting Database
```
/var/www/html# mysql monnet < config/monnet.sql -p
/var/www/html# mysql monnet < config/monnet-inserts.sql -p
```

## Default user : pwd

```
monnet : monnetadmin
```

## Setting crontab

```
$ nano /etc/crontab
*/5 * * * * root /usr/bin/php /var/www/html/monnet-cli.php
```

## CERTS (disable temporaraly due rewriting)

(without password)
```
$ mkdir /var/certs && cd /var/certs 
$ ssh-keygen -m PEM -t rsa -b 4096
```



add /var/certs/id_rsa to cfg[‘certs’] 

## Server to monitor (disable temporaraly due rewriting)

```
$ adduser monnet
$ usermod -aG sudo monnet
$ usermod -aG adm monnet
```

Add to /etc/sudoers

```
monnet ALL=(ALL) NOPASSWD: /sbin/poweroff, /sbin/reboot, /sbin/shutdown
```

From monnet server to server to monitor

```
$ ssh-copy-id -i /var/certs/id_rsa.pub monnet@ip.ip.ip.ip
```


## MAC address, latest oui.csv
https://regauth.standards.ieee.org/standards-ra-web/pub/view.html