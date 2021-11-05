<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/* Db Config */

$cfg_db['dbtype'] = 'mysql';
$cfg_db['dbhost'] = 'localhost';
$cfg_db['dbname'] = 'monnet';
$cfg_db['dbuser'] = '';
$cfg_db['dbpassword'] = '';
$cfg_db['dbprefix'] = '';
$cfg_db['dbcharset'] = 'utf8';

/* Config */

$cfg['web_title'] = 'MonNet';
$cfg['path'] = '';
$cfg['rel_path'] = '';
$cfg['lang'] = 'es';
$cfg['sid_expire'] = 0;
$cfg['theme'] = 'default';
$cfg['css'] = 'default';
$cfg['charset'] = 'utf-8';
$cfg['net'] = '192.168.1.0/24';
/* Modules Config */
/* TODO: Split load config files */
$cfg['weather_widget']['country'] = '';
$cfg['weather_widget']['weather_api'] = '';
