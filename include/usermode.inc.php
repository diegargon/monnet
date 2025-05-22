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

session_start();

$userService = $ctx->get(UserService::class);

if ($userService->getLang() !== 'es') {
    $default_lng = $lng; # Default lang
    $new_lang_file = 'lang/' . $userService->getLang() . '/main.lang.php';
    if (file_exists($new_lang_file)) {
        $user_lng = [];
        include($new_lang_file);
        $user_lng = $lng;
        $lng = array_merge($default_lng, $user_lng);
        $ctx->setLang($lng);
    }
}
