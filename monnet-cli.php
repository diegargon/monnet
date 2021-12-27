<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
define('IN_CLI', true);
//define('DUMP_VARS', true);

/* CONFIG */
$ROOT_PATH = '/var/www/monnet';
$APP_NAME = 'Monnet';
define('CLI_LOCK', '/var/run/' . $APP_NAME . '.lock');
$VERSION = 0.1;

$arp_cmd = '/usr/sbin/arp';

/* END CONFIG */

if (!file_exists($arp_cmd)) {
    echo "Please install arp (net-tools)\n";
    exit();
}

chdir($ROOT_PATH);

require_once('include/common.inc.php');

isset($argv[1]) && $argv[1] == '-console' ? $log->setConsole(true) : null;

$log->debug("Starting {$cfg['app_name']} CLI");

require_once('include/climode.inc.php');

if (is_locked()) {
    $log->debug("CLI Locked skipping");
    die();
}

register_shutdown_function('unlink', CLI_LOCK);

check_known_hosts($hosts);
run_commands($cfg, $db, $hosts);
cron($cfg, $db, $hosts);

$log->debug("Finishing {$cfg['app_name']} CLI");
$log->debug("****************************************************************************************");
exit(0);
