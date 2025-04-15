<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
define('IN_CLI', true);

$APP_NAME = 'monnet-discovery';
/**
 *
 * @var Database $db
 * @var Config $ncfg
 * @var AppContext|null $ctx An instance of AppCtx or null if not defined
 */
require_once 'include/climode.inc.php';

Log::debug("Starting $APP_NAME");

if (is_locked()) {
    Log::notice("CLI Locked skipping");
    die();
}

register_shutdown_function('unlink', CLI_LOCK);
ping_nets($ctx);

if ($db->isConn()) :
    $memory_usage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
    $start_time = $_SERVER["REQUEST_TIME_FLOAT"];
    $execution_time = round(microtime(true) - $start_time, 2);
    $load = sys_getloadavg();
    $cpu_usage = round($load[0], 2);
    //TODO: set not work if not exist or fix config or create the key
    $ncfg->set('discovery_last_run_metrics', " ($memory_usage|$execution_time|$cpu_usage)");
    $ncfg->set('discovery_last_run', date_now());
endif;

Log::debug("[Finishing] $APP_NAME " . datetime_machine() . "");

exit(0);
