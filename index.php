<?php

/**
 * @var AppContext|null $ctx An instance of AppCtx or null if not defined
 */
define('IN_WEB', true);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('include/common.inc.php');
require('include/usermode.inc.php');

$web = new Web($ctx);
$web->run();
