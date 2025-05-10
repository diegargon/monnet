<?php

/**
 *
 * @author diego/@/envigo.net
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
$cfg['path'] = '/var/www/html';
$cfg['rel_path'] = '/';
$cfg['lang'] = 'es';

$cfg['allowed_images_ext'] = ['png', 'jpg', 'jpeg', 'gif', 'ico'];

/* Modules Config */

$cfg['weather_widget']['country'] = 'vigo';
$cfg['weather_widget']['weather_api'] = '89fe8d3a8486486fc682ba97dc28850f';
