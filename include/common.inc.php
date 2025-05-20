<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
use App\Core\AppContext;
use App\Core\DBManager;
use App\Core\ConfigService;

!defined('IN_WEB') ? exit : true;

/**
 *
 * @var array<string, mixed> $cfg
 * @var array<string, string> $cfg_db
 */
require 'App/Autoloader.php';
App\Autoloader::register();

if (!file_exists('config/config.defaults.php')) {
    print 'Missing config.defaults.php. Leaving';
    exit(1);
}
if (!file_exists('config/config.priv.php')) {
    print 'Missing config.priv.php. Leaving';
    exit(1);
}
if (!file_exists('/etc/monnet/config.inc.php')) {
    print 'Missing config.inc.php. Leaving';
    exit(1);
}

require_once 'config/config.priv.php';
require_once 'config/config.defaults.php';
require_once 'Constants/Constants.php';
require_once '/etc/monnet/config.inc.php';
require_once 'include/checks.inc.php';

common_checks($cfg_db, $cfg);

$ctx = new AppContext();
$ctx->setCfg($cfg);
$ctx->setCfgDb($cfg_db);

$db = $ctx->set('DBManager', new DBManager($ctx));

if (!$db->isConnected()) {
    print 'DB connection failed';
    exit(1);
}

/**
 * @var array<string> $lng
 */
/* Get default lang: User settings will overwrite */
require_once 'lang/es/main.lang.php';
$ctx->setLang($lng);

$ncfg = $ctx->set(ConfigService::class, new ConfigService($ctx));
$ncfg->init($cfg);

date_default_timezone_set($ncfg->get('default_timezone'));

require_once 'include/updater.inc.php';
