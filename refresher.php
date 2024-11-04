<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
header('Content-Type: application/json; charset=UTF-8');

/**
 * @var User|null $user An instance of User or null if not defined
 * @var AppContext|null $ctx An instance of Context or null if not defined
 * @var array $lng
 * @var Database|null $db An instance of Database or null if not defined
 * @var array $cfg
 */
require_once 'include/common.inc.php';
require_once 'include/usermode.inc.php';
require_once 'include/refresher-func.php';

