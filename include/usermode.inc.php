<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

do_initial_usermode_checks($cfg);

session_name('monent');
session_start();

require('class/User.php');

$user = new User($cfg, $db);
/* Default lang included in common here we overwrite if necessary */

if ($user->getLang() !== 'es') {
    $main_lang_file = 'lang/' . $user->getLang() . '/main.lang.php';
    if (file_exists($main_lang_file)) {
        require_once($main_lang_file);
    }
}
$ctx->setAppUser($user);

require('class/Web.php');

require('include/pages-func.inc.php');
require('include/pages-post.inc.php');
require('include/pages.inc.php');
require('class/Frontend.class.php');

