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
 * @var array<string, string|int> $cfg_db
 */
require 'App/Autoloader.php';
App\Autoloader::register();

if (!file_exists('config/config.priv.php')) {
    print 'Missing config.priv.php. Leaving';
    exit(1);
}
require_once 'config/config.priv.php';

if (!file_exists($cfg['db_cfg_file'])) {
    print 'Missing config-db.json Leaving';
    exit(1);
}
$json_db_cfg = file_get_contents($cfg['db_cfg_file']);

if ($json_db_cfg === false) {
    exit('Could not read configuration file');
}
$cfg_db = json_decode($json_db_cfg, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    exit(json_last_error_msg());
}

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

require_once 'include/updater.inc.php';
