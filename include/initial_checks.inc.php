<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function do_initial_db_check($cfg_db) {
    $err_empty_msg = ' can\'t be empty check config.inc.php';

    if (empty($cfg_db['dbtype'])) {
        exit('dbtype' . $err_empty_msg);
    }

    if (empty($cfg_db['dbhost'])) {
        exit('dbhost' . $err_empty_msg);
    }

    if (empty($cfg_db['dbname'])) {
        exit('dbname' . $err_empty_msg);
    }

    if (empty($cfg_db['dbuser'])) {
        exit('dbuser' . $err_empty_msg);
    }

    if (empty($cfg_db['dbpassword'])) {
        exit('dbpassword' . $err_empty_msg);
    }
    if (empty($cfg_db['dbcharset'])) {
        exit('dbcharset' . $err_empty_msg);
    }
}

function do_initial_main_vars_checks($cfg) {
    $err_empty_msg = ' can\'t be empty check config.inc.php';
    $err_nofile_msg = ' file/directory not exists';

    if (empty($cfg['path'])) {
        exit('path' . $err_empty_msg);
    }
    if (!is_dir($cfg['path'])) {
        exit($cfg['path'] . $err_nofile_msg);
    }
}

function do_initial_usermode_checks($cfg) {
    $err_empty_msg = ' can\'t be empty check config.inc.php';
    $err_nofile_msg = ' file/directory not exists';
    $err_numeric_msg = ' must be numeric';
    $err_noexists_msg = ' not exists';

    if (empty($cfg['lang'])) {
        exit('lang' . $err_empty_msg);
    }

    $lang_file = $cfg['path'] . '/lang/' . $cfg['lang'] . '/main.lang.php';
    if (!file_exists($lang_file)) {
        exit($lang_file . $err_nofile_msg);
    }

    if (!isset($cfg['sid_expire'])) {
        exit('sid_expire' . $err_empty_msg);
    }

    if (!is_numeric($cfg['sid_expire'])) {
        exit('sid_expire' . $err_numeric_msg);
    }

    if (empty($cfg['css'])) {
        exit('css' . $err_empty_msg);
    }

    if (empty($cfg['theme'])) {
        exit('theme' . $err_empty_msg);
    }
    if (empty($cfg['charset'])) {
        exit('charset' . $err_empty_msg);
    }

    if (!is_dir($cfg['path'] . '/tpl/' . $cfg['theme'])) {
        exit('theme ' . $cfg['theme'] . ' ' . $err_noexists_msg);
    }
}
