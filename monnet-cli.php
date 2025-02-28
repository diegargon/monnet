<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
define('IN_CLI', true);
//define('DUMP_VARS', true);

$APP_NAME = 'monnet-cli';
/**
 * @var Database $db
 * @var Config $ncfg
 * @var AppContext|null $ctx An instance of AppCtx or null if not defined
 */
require_once 'include/climode.inc.php';
require_once 'include/cron.inc.php';

Log::debug("Starting $APP_NAME");

if (is_locked()) :
    Log::debug("CLI Locked skipping");
    die();
endif;

register_shutdown_function('unlink', CLI_LOCK);
if ($ctx) :
    check_known_hosts($ctx);
    cron($ctx);
endif;

//Log::debug($db->getQueryHistory();

if ($db->isConn()) :
    $ncfg->set('cli_last_run', date_now());
endif;

Log::debug("[Finishing] $APP_NAME " . datetime_machine() . "");

exit(0);
