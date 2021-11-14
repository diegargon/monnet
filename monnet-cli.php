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
$CLI_LOCK = '/tmp/' . $APP_NAME . '.lock';
$VERSION = 0.1;

/* END CONFIG */

if (file_exists($CLI_LOCK)) {
    //TODO FALLBACK
    echo "Warning: CLI lock: " . $CLI_LOCK . "\n";
    return false;
} else {
    touch($CLI_LOCK);
}

chdir($ROOT_PATH);

require_once('include/common.inc.php');
require_once('include/climode.inc.php');

cron($cfg, $db);
check_known_hosts($db);
#ping_net($cfg, $db);

unlink($CLI_LOCK);

return true;

