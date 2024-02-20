<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
define('IN_CLI', true);
//define('DUMP_VARS', true);

/* CONFIG */
$ROOT_PATH = '/var/www/monnet';
/* END CONFIG */

$APP_NAME = 'Monnet';
define('CLI_LOCK', '/var/run/' . $APP_NAME . '.lock');
$VERSION = 0.1;

chdir($ROOT_PATH);

require_once('include/common.inc.php');
require_once('include/util.inc.php');

isset($argv[1]) && ($argv[1] == '-console' || $argv[1] == '--console') ? $log->setConsole(true) : null;

$log->debug("Starting {$cfg['app_name']} CLI");

require_once('include/climode.inc.php');

if (is_locked()) {
    $log->debug("CLI Locked skipping");
    die();
}

register_shutdown_function('unlink', CLI_LOCK);

check_known_hosts($log, $db, $hosts);
#run_cmd_db_tasks($cfg, $db, $hosts);
cron($cfg, $log, $db, $hosts);

//$log->debug($db->getQueryHistory();

$db->update('prefs', ['uid' => 0, 'pref_value' => utc_date_now()], ['pref_name' => 'cli_last_run'], 'LIMIT 1');
$log->debug("[Finishing] {$cfg['app_name']} CLI " . datetime_machine() . "");

exit(0);
