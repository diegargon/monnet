<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
/**
 * @var AppContext|null $ctx An instance of AppCtx or null if not defined
 * @var array<string,string> $lng - Default included in common, we overwrite user lang here
 */
usermode_checks($ncfg);

session_name('monnet');
session_start();

$user = $ctx->get('User');
/**
 *
 */
if ($user->getLang() !== 'es') {
    $main_lang_file = 'lang/' . $user->getLang() . '/main.lang.php';
    if (file_exists($main_lang_file)) {
        require_once($main_lang_file);
        $ctx->setLang($lng);
    }
}

require_once 'class/Web.php';
require_once 'include/pages-func.inc.php';
require_once 'include/pages-post.inc.php';
require_once 'include/pages.inc.php';
require_once 'class/Frontend.php';
require_once 'include/net-user.inc.php';
