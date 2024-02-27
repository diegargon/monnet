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

$custom_cfg = '/etc/monnet/config.inc.php';
if (!file_exists($custom_cfg)) {
    echo 'Missing config file ' . $custom_cfg;
    exit(1);
}
require($custom_cfg);

$APP_NAME = 'monnet-cli';
define('CLI_LOCK', '/var/run/' . $APP_NAME . '.lock');
$VERSION = 0.1;

chdir($cfg['path']);

require_once('include/common.inc.php');
require_once('include/util.inc.php');

isset($argv[1]) && ($argv[1] == '-console' || $argv[1] == '--console') ? Log::setConsole(true) : null;

Log::debug("Starting {$cfg['app_name']} CLI");

require_once('include/climode.inc.php');

if (is_locked()) {
    Log::debug("CLI Locked skipping");
    die();
}

register_shutdown_function('unlink', CLI_LOCK);

check_known_hosts($ctx);
#run_cmd_db_tasks($cfg, $db, $hosts);
cron($ctx);

//Log::debug($db->getQueryHistory();

$db->update('prefs', ['uid' => 0, 'pref_value' => utc_date_now()], ['pref_name' => 'cli_last_run'], 'LIMIT 1');
Log::debug("[Finishing] {$cfg['app_name']} CLI " . datetime_machine() . "");

exit(0);
