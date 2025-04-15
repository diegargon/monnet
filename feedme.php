<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);

require_once 'include/common.inc.php';

use App\Controllers\FeedMeController;
$feedMeController = new FeedMeController($ctx);
$feedMeController->handleRequest();
exit;
