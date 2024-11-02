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

$APP_NAME = 'monnet-cli';
/**
 * @var Database $db
 * @var AppCtx|null $ctx An instance of AppCtx or null if not defined
 */
require_once('include/climode.inc.php');

require_once('include/phpsec_helper.inc.php');
require_once('include/cron.inc.php');
require_once('include/ssh.inc.php');
require_once('include/host-access-work.inc.php');

Log::debug("Starting $APP_NAME");

if (is_locked()) {
    Log::debug("CLI Locked skipping");
    die();
}

register_shutdown_function('unlink', CLI_LOCK);
if ($ctx) {
    check_known_hosts($ctx);
#run_cmd_db_tasks($cfg, $db, $hosts);
    cron($ctx);
}
//Log::debug($db->getQueryHistory();

if ($db) {
    $db->update('prefs', ['uid' => 0, 'pref_value' => utc_date_now()], ['pref_name' => 'cli_last_run'], 'LIMIT 1');
}
Log::debug("[Finishing] $APP_NAME " . datetime_machine() . "");

exit(0);
