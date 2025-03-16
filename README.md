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

MonNet is currently in an early version. It is possible that issues may arise between versions. Compatibility is not guaranteed.

I started implementing Ansible as a replacement for phpseclib as a method to connect to and retrieve data from hosts, automate installations and other tasks

# Features

    - Track Host In your network and have an inventory
    - Alarms/Warnings/Events (Agent)
    - A simple bookmarks dashboard
    - Basic IPAM
    - Basic Hosts Stats (Agent)
    - Linux Support (Ansible/Agent)
    - Host Notes
    - Get Host Logs (Via Ansible)
    - Email Alerts

    Partially working Features

    - Execute Playbooks (Ansible)

    Future Features:

    - Windows hosts support
    - Respond to events in hosts with playbooks ((Agent/Ansible)

## Versions Convention

v0.0.0 Mayor.Minor.Revision

Mayor/Minor implies database changes or other code mayor changes.

Revision version implied only code changes, never database changes.

## LICENSE

CC BY-NC-ND 4.0

Resume:

    Non-Commercial Use = Allowed

    Commercial Use = License

## MonNet Install

The automatic/fast method is using the docker-compose.yml

Here the manual process.

## Deps

LAMP
    Linux, Apache, Mysql, PHP
    Tested: Debian 12, Apache2, PHP8.2, Mariadb 10

    That mean in Debian:
    apt install apache2 php-fpm php-mysqli php-curl php-mbstring

Python
    Tested with >3

Mysql/MariaDB:
    You can install or use a remote machine with mysql.

arp
    Optional for get mac's address, only work on the same network (other method will added in the future).

```
    apt install net-tools
```

## Initial database settings

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
/var/www/html# chmod 755 cache logs
```

## Config

Check config/config.defaults.php and add the keywords you want to change to /etc/monnet/config.inc.php.

Avoid copy the file just add the keywords you want or need change

Do not rename or modify config.defaults.php directly, as it will be overwritten.

These are the main config keywords you must check/change and copy to the /etc file.

Warning: path config must included even if the default is valid

Mandatory

```
$cfg_db['dbhost']
$cfg_db['dbname']
$cfg_db['dbuser']
$cfg_db['dbpassword']
$cfg['path'] = '/var/www/html';
```

Optional

```
$cfg['rel_path'] = '/';
$cfg['lang'] = 'es';
```

## Setting the database

```
/var/www/html# mysql monnet < config/monnet.sql -p
```

## Default frontend user : pwd

```
monnet : monnetadmin
```

## Setting crontab

Times depends of the size of your network. Paths depends on your system.

Here is the config to run each task every 5 and 15 minutes.

```
$ nano /etc/crontab
*/5 * * * * root /usr/bin/php /var/www/html/monnet-cli.php
*/15 * * * * root /usr/bin/php /var/www/html/monnet-discovery.php
```

In the future I will migrating that cli tools to Python.

After that, you must configure your network/s clicking on "Netwrok" on the top left panel.

## Composer

Necessary if you want support for send mails.

```
apt install composer

/path/to/monnet# composer require phpmailer/phpmailer
```

# Monnet Core

monnet-core contains monnet-gateway service and the monnet-agent service

- monnet-gateway is currently used only as a gateway to execute ansible commands, more feautres will be added in the future.
It must be installed in the same host.

    Installation instructions:

    https://github.com/diegargon/monnet-core/tree/main/monnet_gateway

- monnet-agent is used as source for install monnet-agent via ansible/monnet-gateway on remote hosts

    Installation instructions:

    https://github.com/diegargon/monnet-core/tree/main/monnet_agent

Ansible support is required on the host.

## Install and configure Ansible

Ansible support is currently an experimental feature. It helps with the agent installation and, in the future, will allow the execution of other common Ansible tasks.

```
apt install ansible
```

## External Resource used (included in sources)

- MAC address, latest oui.csv

    https://regauth.standards.ieee.org/standards-ra-web/pub/view.html

- IANNA Standard port
    https://www.iana.org/assignments/service-names-port-numbers/service-names-port-numbers.csv
