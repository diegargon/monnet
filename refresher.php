<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var UserService $userService An instance of UserService
 * @var User|null $user An instance of User or null if not defined
 * @var AppContext|null $ctx An instance of Context or null if not defined
 * @var array<string,string> $lng
 * @var Database|null $db An instance of Database or null if not defined
 * @var Config $ncfg
 */
use App\Services\UserService;
use App\Controllers\RefresherController;

define('IN_WEB', true);
header('Content-Type: application/json; charset=UTF-8');

require_once 'include/common.inc.php';
require_once 'include/usermode.inc.php';


# Auth Check
$userService = new UserService($ctx);
if (!$userService->isAuthorized()) {
    echo json_encode([
        'error' => 1,
        'error_msg' => 'No autorizado'
    ]);
    exit();
}


$controller = new RefresherController($ctx);
$controller->refreshPage();
exit();
