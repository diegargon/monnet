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
do_initial_main_vars_checks($cfg);

/* phpseclib deps */
require_once 'vendor/autoload.php';

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

if ($cfg_db['dbtype'] == 'mysql') {
    require('class/Mysql.class.php');
}

$db = new Database($cfg_db);
$db->connect();

require_once('include/net.inc.php');
require_once('include/phpsec_helper.inc.php');

/* Get default lang overwrite after with user settings */
require_once('lang/es/main.lang.php');
