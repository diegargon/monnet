<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

if (!file_exists('/etc/monnet/config.inc.php')) {
    exit('Missing config.inc.php. Leaving');
} else {
    include_once '/etc/monnet/config.inc.php';
    if (!isset($cfg['path'])) {
        exit('You must set $cfg["path" in /etc/monnet/config.inc.php');
    }
}

/**  @var string $APP_NAME defined in monnet-cli or monnet-discovery */
define('CLI_LOCK', '/var/run/' . $APP_NAME . '.lock');

/**
 *  @var array<int|string, mixed> $cfg load in config.inc.php
 */
chdir($cfg['path']);

require_once 'include/common.inc.php';

isset($argv[1]) && ($argv[1] == '-console' || $argv[1] == '--console') ? Log::setConsole(true) : null;
function is_locked(): bool
{

    if (@symlink("/proc/" . getmypid(), CLI_LOCK) !== false) {
        return false;
    }

    if (is_link(CLI_LOCK) && !is_dir(CLI_LOCK)) {
        unlink(CLI_LOCK);

        return is_locked();
    }

    return true;
}

require_once 'include/commands.inc.php';
require_once 'include/mac_vendor.inc.php';
require_once 'include/curl.inc.php';
require_once 'include/net-cli.inc.php';
