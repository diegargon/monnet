<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

require_once('config/config.priv.php');
/* Db Config */

$cfg_db['dbtype'] = 'mysql';
$cfg_db['dbhost'] = 'localhost';
$cfg_db['dbname'] = 'monnet';
$cfg_db['dbuser'] = 'monnet';
$cfg_db['dbpassword'] = '';
$cfg_db['dbprefix'] = '';
$cfg_db['dbcharset'] = 'utf8';

/* Config */

$cfg['web_title'] = 'Monnet';
$cfg['path'] = '/var/www/html';
$cfg['rel_path'] = '/';
$cfg['lang'] = 'es';
$cfg['sid_expire'] = 99999;
$cfg['theme'] = 'default';
$cfg['css'] = 'default';
$cfg['charset'] = 'utf-8';
$cfg['net'] = '192.168.1.0/24';

$cfg['cert'] = '/home/monnet/.ssh/id_rsa';

/* Modules Config */
/* TODO: Split load config files */
$cfg['weather_widget']['country'] = 'vigo';
$cfg['weather_widget']['weather_api'] = '89fe8d3a8486486fc682ba97dc28850f';
