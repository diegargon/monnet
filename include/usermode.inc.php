<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var AppContext|null $ctx An instance of AppCtx or null if not defined
 * @var array<string,string> $lng - Default included in common, we overwrite user lang here
 */
use App\Services\UserService;
!defined('IN_WEB') ? exit : true;

usermode_checks($ncfg);

session_start();

$userService = new UserService($ctx);

$user = $ctx->get('User');

if ($user->getLang() !== 'es') {
    $main_lang_file = 'lang/' . $user->getLang() . '/main.lang.php';
    if (file_exists($main_lang_file)) {
        require_once($main_lang_file);
        $ctx->setLang($lng);
    }
}

require_once 'class/Frontend.php';
