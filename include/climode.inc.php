<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

$custom_cfg = '/etc/monnet/config.inc.php';
if (!file_exists($custom_cfg)) {
    echo 'Missing config file ' . $custom_cfg;
    exit(1);
}
require($custom_cfg);

define('CLI_LOCK', '/var/run/' . $APP_NAME . '.lock');

chdir($cfg['path']);

require_once('include/common.inc.php');

isset($argv[1]) && ($argv[1] == '-console' || $argv[1] == '--console') ? Log::setConsole(true) : null;

function is_locked() {

    if (@symlink("/proc/" . getmypid(), CLI_LOCK) !== FALSE) {
        return false;
    }

    if (is_link(CLI_LOCK) && !is_dir(CLI_LOCK)) {
        unlink(CLI_LOCK);

        return is_locked();
    }

    return true;
}

require_once('include/commands.inc.php');
require_once('include/mac_vendor.inc.php');
require_once('include/curl.inc.php');
require_once('include/net-cli.inc.php');
