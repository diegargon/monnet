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
    - Alarms/Events (Agent)
    - A simple bookmarks dashboard
    - Basic IPAM
    - Basic Hosts Stats (Agent)
    - Linux Support (Ansible/Agent)
    - Host Notes
    - Get Host Logs (Via Ansible)
    - Email Alerts

    Partially working Features

    - Execute Playbooks (Ansible)
    - Respond to events in hosts with playbooks ((Agent/Ansible)

    Future Features:

    - Windows hosts support


## Versions Convention

v0.0.0 Mayor.Minor.Revision

Mayor/Minor implies database changes or other code mayor changes.

Revision version implied only code changes, never database changes.

## LICENSE

CC BY-NC-ND 4.0

Resume:

    Non-Commercial Use = Allowed

    Commercial Use = Ask

# MonNet Install

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

Ansible support with the agent

```
git clone https://github.com/diegargon/monnet-core /opt/monnet-ansible
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

## Monne Agent

Currently, I am testing a basic linux agent (python based) for reports.
The agent is the  monnet-core repo/sources and has its own playbook to automatically install if you want.

You can install it manually by checking the install-agent-linux playbook for steps.

Python: The automatic process will install on the hosts some deps: psutils.

## Ansible Support

Ansible support is a testing feature; it will help to install the agent and, in the future, crate and perform other common "Ansible tasks"

# Install ansible

```
apt install ansible
```

# Install Monnet Ansible (monnet-core)

```
git clone https://github.com/diegargon/monnet-core.git
```

## Ansible server

Ansible server listens on localhost only; it is a testing feature without security.
You must install ansible on the same system.

Ansible must output in JSON format.

```
nano /etc/ansible/ansible.cfg

[defaults]
stdout_callback=json
```

## Ansible client hosts

By default, the Ansible SSH user will be 'ansible'.

Must be/have:

    * Be a sudo member without need to type a password
    * Have the public SSH key installed

Example

```
apt install sudo
adduser --disabled-password ansible
usermod -aG sudo ansible
```

Start 'visudo' and add:

```
ansible ALL=(ALL) NOPASSWD: ALL
```

# Fedora

```
sudo adduser ansible
sudo usermod -aG wheel ansible
```

You must have "Ansible Support" checked in the General configuration tab and "Ansible Support" in the host configuration section (Web UI).

## SSH CERTS

For the Ansible server to connect to the hosts, you need to generate an SSH key and install it on each host you want to access via MonNet/Ansible.

```
$ ssh-keygen -m PEM -t rsa -b 4096
$ ssh-copy-id -i ~/.ssh/id_rsa.pub ansible@ip.ip.ip.ip
```

The user must exist and must be allowed to log in with standard credentials to install the key (you can disable it after).

Or do it manually on the client host:

```
runuser -u ansible mkdir /home/ansible/.ssh
runuser -u ansible nano /home/ansible/.ssh/authorized_keys
```

And paste the SSH public key.

If you don't use ssh-copy-id you must manually add the key to the known_host file (Monnet server side).

```
ssh-keyscan -t ecdsa,ed25519 -H server.example.com >> ~/.ssh/known_hosts 2>&1
```

If the host fingerprint change you must first remove the old one

```
ssh-keygen -R
```

You can force Ansible to ignore the host fingerprint check.

```
[defaults]
host_key_checking = False
```

## External Resource used (included in sources)

## MAC address, latest oui.csv
https://regauth.standards.ieee.org/standards-ra-web/pub/view.html
## IANNA Standard port
https://www.iana.org/assignments/service-names-port-numbers/service-names-port-numbers.csv
