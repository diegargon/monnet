![GithubTest](https://img.shields.io/badge/Github-TestSelfK8s-blue)
[![PhpUnit](https://github.com/diegargon/monnet/actions/workflows/k8s-phpunit.yml/badge.svg)](https://github.com/diegargon/monnet/actions/workflows/k8s-phpunit.yml)
[![PhpStan](https://github.com/diegargon/monnet/actions/workflows/k8s-phpstan.yml/badge.svg)](https://github.com/diegargon/monnet/actions/workflows/k8s-phpstan.yml)
[![CodeSniffer](https://github.com/diegargon/monnet/actions/workflows/k8s-codesniffer.yml/badge.svg)](https://github.com/diegargon/monnet/actions/workflows/k8s-codesniffer.yml)
[![License: CC BY-NC-ND 4.0](https://img.shields.io/badge/License-CC_BY--NC--ND_4.0-lightgrey.svg)](https://creativecommons.org/licenses/by-nc-nd/4.0/)
###

# MonNet

MonNet is app (web based) to a preview/summary  your network, featuring additional options.

<p align="center">
<img width="400" src="https://github.com/diegargon/monnet/blob/main/monnet.png?raw=true" width="100%">
</p>

## MonNet Status

MonNet is currently in an early version. issues may arise between versions. Compatibility is not guaranteed.

Since Monnet is becoming more complex, i'm working in rewrite nearly all code, beware.

I started implementing Ansible as a replacement for phpseclib as a method to connect to and retrieve data from hosts, automate installations and other tasks

## Breaking/Mayor changes

* MonNet now use /etc/monnet/config-db.json configuration file, the same as Monnet Gateway, check 'Config' section
* MonNet not uses the CRON scripts anymore, now MonNet Gateway is in charge, so the two CRON scripts need to be removed.

# Features

    - Track Host In your network and have an inventory
    - Alarms/Warnings/Events (Agent)
    - A simple bookmarks dashboard
    - Basic IPAM
    - Basic Hosts Stats
    - Linux Support
    - Host Notes
    - Get Host data, and logs via Ansible.
    - Email Alerts

    Partially working Features

    - Manually execute Ansible Playbooks
    - Program Ansible Tasks

    Future Features:

    - Windows hosts support
    - Respond to events in hosts with playbooks ((Agent/Ansible)

## Starting (Basic)

For basic usage after the install process, you must add at least one network to monitor in top-right menu: General->Networks

## Versions Convention

v0.0.0 Mayor.Minor.Revision

Revision version implied only code changes, never database changes.

Mayor.Minor more relevant changes

## LICENSE

CC BY-NC-ND 4.0

Resume:

    Non-Commercial Use = Allowed

    Commercial Use = Ask

## MonNet Install

The automatic/fast method is using the docker-compose.yml

Here the manual process.

## MonNet Update

At this momment only 'git pull' is avaible, will handle database updates.

IMPORTANT:

You must stop the monnet-gateway.service before upgrade MonNet. Certain database upgrades can
get locked due if the monnet-gateway service is running.

That is the safest way. If you forget you always can restart via UI or command line

In a systemd SO

```
systemctl stop monnet
```

do the git pull on monnet directory

```
systemctl start monnet
```


## Deps

LAMP
    Linux, Apache, Mysql, PHP
    Tested: Debian 12, Apache2, PHP8.2, Mariadb 10

    That mean in Debian:
    apt install apache2 php-fpm php-mysqli php-curl php-mbstring

Python
    Tested with >=3.9

Mysql/MariaDB:
    Tested MySQL >= 8
    You can install locally or use a remote machine with mysql.

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

Create the file /etc/monnet/config-db.json

```
{
    "python_driver": "mysql-connector",
    "dbhost": "localhost",
    "dbport": 3306,
    "dbname": "monnet",
    "dbuser": "root",
    "dbpassword": "mydbpass",
    "dbtype": "mysqli",
    "dbcharset": "utf8"
}
```

"python_driver" is relate to Monnet Gateway

## Setting the database

```
/var/www/html# mysql monnet < config/monnet.sql -p
```

## Default frontend user : pwd

```
monnet : monnetadmin
```

## Code Stats 25/05


 *.php lines
```
 find . -name '*.php' -type f -exec wc -l {} +
```

25857 total

```
find .  -type f |wc -l
```
425

# Monnet Core

monnet-core contains monnet-gateway service and the monnet-agent service

- monnet-gateway is currently used as a gateway to execute ansible commands, scan, discovery more feautres will be added in the future.
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
