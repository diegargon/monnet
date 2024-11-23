![GithubTest](https://img.shields.io/badge/Github-TestSelfK8s-blue)
[![PhpUnit](https://github.com/diegargon/monnet/actions/workflows/k8s-phpunit.yml/badge.svg)](https://github.com/diegargon/monnet/actions/workflows/k8s-phpunit.yml)
[![PhpStan](https://github.com/diegargon/monnet/actions/workflows/k8s-phpstan.yml/badge.svg)](https://github.com/diegargon/monnet/actions/workflows/k8s-phpstan.yml)
[![CodeSniffer](https://github.com/diegargon/monnet/actions/workflows/k8s-codesniffer.yml/badge.svg)](https://github.com/diegargon/monnet/actions/workflows/k8s-codesniffer.yml)
[![License: CC BY-NC-ND 4.0](https://img.shields.io/badge/License-CC_BY--NC--ND_4.0-lightgrey.svg)](https://creativecommons.org/licenses/by-nc-nd/4.0/)
###

# MonNet

MonNet is a hybrid of a homepage accessible from your web browser and a preview/summary of your network, featuring additional options.

<p align="center">
<img width="400" src="https://github.com/diegargon/monnet/blob/main/monnet.png?raw=true" width="100%">
</p>

## MonNet Status

MonNet is currently in an early version. It is possible that issues may arise between versions. The compatibility is not guaranteed.

The English language (EN/US) is IA generated.

## Versions

v0.0.0 Mayor.Minor.Revision

Mayor/Minor implies database changes or other code mayor changes
Revision version implied only code changes, never database changes.


# MonNet Install

## Deps

LAMP
    Linux, Apache, Mysql, PHP
    Tested: Debian 12, Apache2, PHP8.2, Mariadb 10

Composer
arp
    Optional for get mac's address, only work same network (other method will added in the future)

phpseclib (Composer)
    Features is disable due rewriting. Thinking if port CLI scripts to python.

## Initial  database settings
```
mysql -u root -p
mysql> CREATE DATABASE monnet;
mysql> CREATE USER 'monnet'@'localhost' IDENTIFIED BY 'password';
mysql> GRANT ALL PRIVILEGES ON monnet.* TO 'monnet'@'localhost'
```

## Clone repo
```
/var/www/html# git clone https://github.com/diegargon/monnet .

/var/www/html# chown -R www-data:www-data *
/var/www/html# chmod 755 cache
```

## Composer deeps

```
#
/path/to/monnet# composer require phpseclib/phpseclib:~3.0
#optional for send messsages (not yet)
/path/to/monnet# composer require phpmailer/phpmailer
```

## Config

Check config/config.defaults.php and add the keywords you want to change to /etc/monnet/config.inc.php.

If you copy the file, it’s better to remove all unchanged config entries.

Do not rename or modify config.defaults.php directly, as it will be overwritten.

These are the main config keywords you must check/change and copy to the /etc file.

```
$cfg_db['dbhost']
$cfg_db['dbname']
$cfg_db['dbuser']
$cfg_db['dbpassword']
$cfg['path'] = '/var/www/html';
$cfg['rel_path'] = '/';
$cfg['lang'] = 'es';
```

## Load the sql

```
/var/www/html# mysql monnet < config/monnet.sql -p
```

## Default user : pwd

```
monnet : monnetadmin
```

## Setting crontab

Times depends of your network

```
$ nano /etc/crontab
*/5 * * * * root /usr/bin/php /var/www/html/monnet-cli.php
*/15 * * * * root /usr/bin/php /var/www/html/monnet-discovery.php
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
## IANNA Standard port
https://www.iana.org/assignments/service-names-port-numbers/service-names-port-numbers.csv