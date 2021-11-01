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
$VERSION = 0.1;

/* END CONFIG */

chdir($ROOT_PATH);

require_once('include/common.inc.php');
require_once('include/climode.inc.php');

cron($db);

return true;

