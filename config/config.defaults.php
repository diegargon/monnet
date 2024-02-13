<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/* Db Config */

$cfg_db['dbtype'] = 'mysql';
$cfg_db['dbhost'] = 'localhost';
$cfg_db['dbname'] = 'monnet';
$cfg_db['dbuser'] = 'monnet';
$cfg_db['dbpassword'] = '';
$cfg_db['dbprefix'] = '';
$cfg_db['dbcharset'] = 'utf8';

/* Config */
$cfg['app_name'] = 'monnet';
$cfg['web_title'] = 'MonNet';
$cfg['path'] = '/var/www/html';
$cfg['rel_path'] = '/';
$cfg['lang'] = 'es';
$cfg['sid_expire'] = 99999;
$cfg['theme'] = 'default';
$cfg['css'] = 'default';
$cfg['charset'] = 'utf-8';
$cfg['graph_charset'] = 'es-ES';
$cfg['log_level'] = 'LOG_WARN';
$cfg['log_file'] = 'logs/monnet.log';
$cfg['log_to_syslog'] = 0;
$cfg['log_to_db'] = 1;
$cfg['log_to_file'] = 1;
$cfg['log_file_owner'] = 'www-data';
$cfg['log_file_owner_group'] = 'www-data';
/*
  'LOG_EMERG' => 0    'LOG_ALERT' => 1    'LOG_CRIT' => 2
  'LOG_ERR' => 3      'LOG_WARNING' => 4  'LOG_NOTICE' => 5
  'LOG_INFO' => 6     'LOG_DEBUG' => 7
 */
$cfg['term_log_level'] = 7;
$cfg['term_system_log_level'] = 5;
$cfg['term_max_lines'] = 200;
$cfg['term_show_system'] = 'LOG_ERR'; // Empty for no or LOG_LEVEL, need log_to_db
$cfg['term_date_format'] = '[d][H:i:s]';
$cfg['timezone'] = 'UTC';
$cfg['date_format'] = 'd-m-Y';
$cfg['time_format'] = 'H:i:s';
$cfg['datetime_format'] = 'd-m-Y H:i:s';
$cfg['datetime_format_min'] = 'd/H:i';
$cfg['datatime_graph_format'] = 'H:i';
$cfg['datetime_log_format'] = 'd-m H:i:s';
$cfg['refresher_time'] = 5; //minutes ideally same minutes than monnet-cli
$cfg['cert'] = '/var/certs/id_rsa';

/* Modules Config */
/* TODO: Split load config files */
$cfg['weather_widget']['country'] = 'vigo';
$cfg['weather_widget']['weather_api'] = '89fe8d3a8486486fc682ba97dc28850f';
