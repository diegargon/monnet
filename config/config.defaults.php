<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
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
$cfg['charset'] = 'utf-8';
$cfg['graph_charset'] = 'es-ES';
$cfg['check_retries'] = 4;                # Ping/Port retrys to  mark host down
$cfg['check_retries_usleep'] = 500000;    # Next attempt wait usec
/* Log and Term Logs */
/* Date */
$cfg['term_date_format'] = '[d][H:i]';
$cfg['timezone'] = 'UTC';
$cfg['date_format'] = 'd-m-Y';
$cfg['time_format'] = 'H:i:s';
$cfg['datetime_format'] = 'd-m-Y H:i:s';
$cfg['datetime_format_min'] = 'd/H:i';
$cfg['datatime_graph_format'] = 'H:i';
$cfg['datetime_log_format'] = 'd-m-y H:i:s';

//Web UI refresh time
$cfg['allowed_images_ext'] = ['png', 'jpg', 'jpeg', 'gif', 'ico'];

/* Modules Config */

$cfg['weather_widget']['country'] = 'vigo';
$cfg['weather_widget']['weather_api'] = '89fe8d3a8486486fc682ba97dc28850f';
