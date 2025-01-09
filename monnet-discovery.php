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
    Log::debug("CLI Locked skipping");
    die();
}

register_shutdown_function('unlink', CLI_LOCK);
ping_nets($ctx);

if ($db->isConn()) :
    $ncfg->set('discovery_last_run', date_now());
/*
    $db->update(
        'prefs',
        ['uid' => 0, 'pref_value' => date_now()],
        ['pref_name' => 'discovery_last_run'],
        'LIMIT 1'
    );
  */
endif;

Log::debug("[Finishing] $APP_NAME " . datetime_machine() . "");

exit(0);
