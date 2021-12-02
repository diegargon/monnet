<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

do_initial_usermode_checks($cfg);

session_name('monent');
session_start();

require('class/Filters.class.php');
require('class/User.class.php');

$user = new User($cfg, $db);
/* Default lang included in common here we overwrite if necessary */

if ($user->getLang() !== 'es') {
    $main_lang_file = 'lang/' . $user->getLang() . '/main.lang.php';
    if (file_exists($main_lang_file)) {
        require_once($main_lang_file);
    }
}

require('class/Web.class.php');
require('include/pages.inc.php');
require('class/Frontend.class.php');

