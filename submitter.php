<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);

header('Content-Type: application/json; charset=UTF-8');

/**
 *
 * @var User|null $user An instance of User or null if not defined
 * @var AppContext|null $ctx An instance of Context or null if not defined
 * @var array<string,string> $lng
 * @var Database|null $db An instance of Database or null if not defined
 * @var array<int|string, mixed> $cfg
 * @var Config $ncfg
 */
require_once 'include/common.inc.php';
require_once 'include/common-call.php';
require_once 'include/usermode.inc.php';
require_once 'include/submitter-call.php';

use App\Router\CommandRouter;
$cmdRouter = new CommandRouter($ctx);

$command = Filters::postString('command');
$command_values = Filters::sanArray('command_values', 'post');
$response = $cmdRouter->handleCommand($command, $command_values);
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit();
