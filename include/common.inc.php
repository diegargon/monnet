<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

date_default_timezone_set('UTC');

require('config/config.inc.php');
require('include/initial_checks.inc.php');
do_initial_db_check($cfg_db);

if ($cfg_db['dbtype'] == 'mysql') {
    require('class/Mysql.class.php');
}

$db = new Database($cfg_db);
$db->connect();

/* Get default lang overwrite after with user settings */
require_once('lang/es/main.lang.php');
