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

The English language (EN/US) is AI generated.

I started implementing Ansible as a replacement for phpseclib as a method to connect to and retrieve data from hosts.

## Versions Convention

v0.0.0 Mayor.Minor.Revision

Mayor/Minor implies database changes or other code mayor changes
Revision version implied only code changes, never database changes.

## LICENSE

CC BY-NC-ND 4.0

Resume:

    Non-Commercial Use = Allowed

    Commercial Use = License

# MonNet Install

## Deps

LAMP
    Linux, Apache, Mysql, PHP
    Tested: Debian 12, Apache2, PHP8.2, Mariadb 10

    That mean in Debian:
    apt install apache2 php-fpm php-mysqli php-curl php-mbstring

Mysql/MariaDB:
    You can install or use a remote machine

arp
    Optional for get mac's address, only work same network (other method will added in the future)

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

## Setting the database

```
/var/www/html# mysql monnet < config/monnet.sql -p
```

## Default frontend user : pwd

```
monnet : monnetadmin
```

## Setting crontab

Times depends of the size of your network. Paths depends of your system

Here the config for run every 5 and 15 minutes each task

```
$ nano /etc/crontab
*/5 * * * * root /usr/bin/php /var/www/html/monnet-cli.php
*/15 * * * * root /usr/bin/php /var/www/html/monnet-discovery.php
```

## Composer

Necessary if you want support for send mail

```
apt install composer

/path/to/monnet# composer require phpmailer/phpmailer
```

## Monne Agent

Testing a basic linux agent (python based) for reports. Shipping with monnet-ansbile
repo/sources and his own playbook to install.

You can install it manually check the install-agent-linux playbook for steps

Python Extras: Automatic process will install: psutils

## Ansible Support

Ansible support its a testing feature, it will help to install the agent and in
the future, crate and doing other common "ansible tasks"

# Install ansible

```
apt install ansible
```

# Install monnet-ansible

```
git clone https://github.com/diegargon/monnet-ansible.git
```


## Ansible server

Ansible server listen in localhost only, it is a testing feature without security.
You must install ansible in the same Monnet system.

Ansible must output in json format.

```
nano /etc/ansible/ansible.cfg

[defaults]
stdout_callback=json
```



## Ansible client hosts

By default the ansible ssh user will be 'ansible'

Must be/have:

    * sudo member without need type password
    * have the public ssh key installed

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

You must have checking the "Ansible Support" in General configuration tab and "Ansible Support" in the host configuration
section (Web UI)

## SSH CERTS

For Ansible server to connect to the hosts, you need to generate an SSH key and install it on each host you want to access via Monnet/Ansible.
```
$ ssh-keygen -m PEM -t rsa -b 4096
$ ssh-copy-id -i ~/.ssh/id_rsa.pub root@ip.ip.ip.ip
```

Or do it manually

On the client host:

```
runuser -u ansible mkdir /home/ansible/.ssh
runuser -u ansible nano /home/ansible/.ssh/authorized_keys
```
and paste the ssh pub key

If you don't use ssh-copy-id you must add manually the known_host (Monnet server side)

```
ssh-keyscan -t ecdsa,ed25519 -H server.example.com >> ~/.ssh/known_hosts 2>&1
```

If the host fingerprint change you must remove first the old one

```
ssh-keygen -R
```

You can force ansible to ignore the host fingerprint check

```
[defaults]
host_key_checking = False
```

## External Resource used (included in sources)

## MAC address, latest oui.csv
https://regauth.standards.ieee.org/standards-ra-web/pub/view.html
## IANNA Standard port
https://www.iana.org/assignments/service-names-port-numbers/service-names-port-numbers.csv
