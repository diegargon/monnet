<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

require_once('include/net.inc.php');
require_once('include/cronjobs.inc.php');
require_once('include/cron.inc.php');

/* phpseclib deps */
require_once 'vendor/autoload.php';

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

function is_locked() {

    if (@symlink("/proc/" . getmypid(), CLI_LOCK) !== FALSE) {
        return false;
    }

    if (is_link(CLI_LOCK) && !is_dir(CLI_LOCK)) {
        unlink(CLI_PCK);

        return is_locked();
    }

    return true;
}
