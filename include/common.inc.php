<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/**
 * @var array<string|int> $cfg
 */
/**
 * @var array<string|int> $cfg_db
 */
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

require_once('class/AppContext.php');

if ($cfg_db['dbtype'] == 'mysqli') {
    require_once('class/Mysql.php');
}

$ctx = new AppContext($cfg);

$db = $ctx->set('Mysql',  new Database($cfg_db));

$db->connect();

require_once('class/Log.php');

/**
 * @var array<string> $lng
 */
/* Get default lang overwrite after with user settings */
require_once('lang/es/main.lang.php');
$ctx->setLang($lng);

require_once('class/Lang.php');
Log::init($cfg, $db, $lng);

require_once('class/Filters.php');
require_once('include/util.inc.php');
require_once('include/time.inc.php');
require_once('include/updater.inc.php');
