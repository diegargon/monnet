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

/* CONFIG */
$ROOT_PATH = dirname(__FILE__);
$APP_NAME = 'Monnet';
define('CLI_LOCK', '/var/run/' . $APP_NAME . '.lock');

$VERSION = 0.1;

/* END CONFIG */

chdir($ROOT_PATH);

require_once('include/common.inc.php');
require_once('include/climode.inc.php');

if (is_locked()) {
    echo "CLI Locked\n";
    die();
}

register_shutdown_function('unlink', CLI_LOCK);

cron($cfg, $db);
check_known_hosts($db);

host_access($cfg, $db);
run_commands($cfg, $db);

exit(0);
