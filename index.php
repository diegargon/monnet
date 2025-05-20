<?php

/**
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var AppContext|null $ctx Instance of AppCtx. Init in common.inc
 */
define('IN_WEB', true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Controllers\Web;

require('include/common.inc.php');
require('include/usermode.inc.php');

$web = new Web($ctx);
$web->run();
