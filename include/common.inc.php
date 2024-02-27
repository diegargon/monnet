<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

if (!file_exists('/etc/monnet/config.inc.php')) {
    print 'Missing config.inc.php. Leaving';
    exit(1);
}
if (!file_exists('config/config.defaults.php')) {
    print 'Missing config.defaults.php. Leaving';
    exit(1);
}

require_once('config/config.priv.php');
require_once('config/config.defaults.php');
require('/etc/monnet/config.inc.php');

date_default_timezone_set($cfg['timezone']);

require_once('include/initial_checks.inc.php');
do_initial_db_check($cfg_db);
do_initial_main_vars_checks($cfg);

if ($cfg_db['dbtype'] == 'mysql') {
    require_once('class/Mysql.php');
}

$db = new Database($cfg_db);
$db->connect();

require_once('class/Log.php');

/* Get default lang overwrite after with user settings */
require_once('lang/es/main.lang.php');
Log::init($cfg, $db, $lng);

require_once('class/Filters.php');
require_once('include/util.inc.php');
require_once('include/time.inc.php');
require_once('include/updater.inc.php');

require_once('include/net.inc.php');
require_once('class/Hosts.php');
require_once('class/Items.php'); // TODO to usermode?
require_once('class/Categories.php');
require_once('class/Networks.php');

require_once('class/AppCtx.php');

$ctx = new AppCtx($cfg, $lng, $db);

$hosts = new Hosts($ctx);
$ctx->setAppHosts($hosts);
