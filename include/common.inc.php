<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

if (!empty($DEBUG)) {
    ini_set("xdebug.var_display_max_children", '-1');
    ini_set("xdebug.var_display_max_data", '-1');
    ini_set("xdebug.var_display_max_depth", '-1');
}
error_reporting(E_ALL);
if (!file_exists('config/config.inc.php')) {
    print 'Missing config.inc.php. Leaving';
    exit(1);
}
if (!file_exists('config/config.defaults.php')) {
    print 'Missing config.defaults.php. Leaving';
    exit(1);
}

require_once('config/config.priv.php');
require_once('config/config.defaults.php');
require_once('config/config.inc.php');

date_default_timezone_set($cfg['timezone']);

require_once('include/initial_checks.inc.php');
do_initial_db_check($cfg_db);
do_initial_main_vars_checks($cfg);

if ($cfg_db['dbtype'] == 'mysql') {
    require_once('class/Mysql.class.php');
}

$db = new Database($cfg_db);
$db->connect();

require_once('class/Log.class.php');

/* Get default lang overwrite after with user settings */
require_once('lang/es/main.lang.php');
Log::init($cfg, $db, $lng);

require_once('class/Filters.class.php');
require_once('include/util.inc.php');
require_once('include/time.inc.php');
require_once('include/updater.inc.php');

require_once('include/net.inc.php');
require_once('class/Hosts.class.php');
require_once('class/Items.class.php'); // TODO to usermode?
require_once('class/Categories.class.php');

$hosts = new Hosts($db, $lng);
