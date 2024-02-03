<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

require_once('include/net.inc.php');
require_once('include/commands.inc.php');
require_once('include/phpsec_helper.inc.php');
require_once('include/curl.inc.php');
require_once('mac_vendor.inc.php');
require_once('include/cronjobs.inc.php');
require_once('include/cron.inc.php');

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

function cli_emerg(Log $log) {
    $log->emerg('Error fatal in cli mode');
}

set_error_handler('cli_emerg');

require_once('include/ssh.inc.php');
require_once('include/host-access-work.inc.php');
