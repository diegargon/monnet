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
use App\Router\CommandRouter;
use App\Services\Filter;
define('IN_WEB', true);

header('Content-Type: application/json; charset=UTF-8');

require_once 'include/common.inc.php';
require_once 'include/usermode.inc.php';

# Auth Check
if (!$userService->isAuthorized()) {
    echo json_encode([
        'error' => 1,
        'error_msg' => 'No autorizado'
    ]);
    exit();
}

$cmdRouter = new CommandRouter($ctx);

$command = Filter::postString('command');
$command_values = Filter::sanArray('command_values', 'post');
$response = $cmdRouter->handleCommand($command, $command_values);
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit();
