<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
global $cfg_db;
/* Db Config */

$cfg_db['dbtype'] = 'mysqli'; //do not change
$cfg_db['dbhost'] = '172.18.0.1';
$cfg_db['dbname'] = 'monnet';
$cfg_db['dbuser'] = 'root';
$cfg_db['dbpassword'] = 'monnetadmin';
$cfg_db['dbprefix'] = '';
$cfg_db['dbcharset'] = 'utf8';

/* General  Config */
$cfg['web_title'] = 'MonNet';
$cfg['path'] = '/var/www/html';
$cfg['rel_path'] = '/';
$cfg['lang'] = 'es';
$cfg['sid_expire'] = 99999;
$cfg['theme'] = 'default';
$cfg['css'] = 'default';
$cfg['charset'] = 'utf-8';
$cfg['graph_charset'] = 'es-ES';
$cfg['check_retries'] = 4;                # Ping/Port retrys to  mark host down
$cfg['check_retries_usleep'] = 500000;    # Next attempt wait usec
/* Log and Term */
$cfg['log_level'] = 'LOG_WARN';
$cfg['log_file'] = 'logs/monnet.log';
$cfg['log_to_syslog'] = 0;
$cfg['log_to_db'] = 1;
$cfg['log_to_db_debug'] = 0; //Beware
$cfg['log_to_file'] = 1;
$cfg['log_file_owner'] = 'www-data';
$cfg['log_file_owner_group'] = 'www-data';
/*
  'LOG_EMERGENCY' => 0    'LOG_ALERT' => 1    'LOG_CRITICAL' => 2
  'LOG_ERROR' => 3      'LOG_WARNING' => 4  'LOG_NOTICE' => 5
  'LOG_INFO' => 6     'LOG_DEBUG' => 7
 */
$cfg['term_log_level'] = 7;
$cfg['term_system_log_level'] = 5;
$cfg['term_max_lines'] = 100;
$cfg['term_show_system_logs'] = 'LOG_ERROR'; // Empty for no or LOG_LEVEL, need log_to_db
/* Date */
$cfg['term_date_format'] = '[d][H:i]';
$cfg['timezone'] = 'UTC';
$cfg['date_format'] = 'd-m-Y';
$cfg['time_format'] = 'H:i:s';
$cfg['datetime_format'] = 'd-m-Y H:i:s';
$cfg['datetime_format_min'] = 'd/H:i';
$cfg['datatime_graph_format'] = 'H:i';
$cfg['datetime_log_format'] = 'd-m-y H:i:s';

/* TIMEOUTS */
$cfg['port_timeout_local'] = 0.5;           # sec
$cfg['port_timeout'] = 0.8;                 #sec
$cfg['ping_nets_timeout'] = 200000;         # usec
$cfg['ping_hosts_timeout'] = 400000;        # usec
$cfg['ping_local_hosts_timeout'] = 300000;  # usec

//Web UI refresh time
$cfg['refresher_time'] = 2;
$cfg['allowed_images_ext'] = ['png', 'jpg', 'jpeg', 'gif', 'ico'];
$cfg['stats_clear_days'] = 30;
$cfg['logs_clear_days'] = 30;
$cfg['glow_time'] = 10; // Minutes. Glow host time
/*
 * Agent interval in seconds, inyected in config file, must
 * reinstall to take efect
 */
$cfg['agent_default_interval'] = 30;
$cfg['agent_allow_selfcerts'] = true;

/* Modules Config */

$cfg['weather_widget']['country'] = 'vigo';
$cfg['weather_widget']['weather_api'] = '89fe8d3a8486486fc682ba97dc28850f';
