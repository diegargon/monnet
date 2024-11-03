<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
/**
 * @var array<string|int> $cfg An instance of Config or null if not defined
 * @var AppContext|null $ctx An instance of AppCtx or null if not defined
 */
do_initial_usermode_checks($cfg);

session_name('monnet');
session_start();

$user = $ctx->get('User');
/* Default lang included in common here we overwrite if necessary */

if ($user->getLang() !== 'es') {
    $main_lang_file = 'lang/' . $user->getLang() . '/main.lang.php';
    if (file_exists($main_lang_file)) {
        require_once($main_lang_file);
        $ctx->setLang($lng);
    }
}

require('class/Web.php');

require_once('include/pages-func.inc.php');
require_once('include/pages-post.inc.php');
require_once('include/pages.inc.php');
require_once('class/Frontend.php');
require_once('include/net-user.inc.php');
