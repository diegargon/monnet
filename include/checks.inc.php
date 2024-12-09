<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/**
 *
 * @param array<string,string> $cfg_db
 * @param array<string,string|int|array<int|string,int|string>> $cfg
 * @return void
 */
function common_checks(array $cfg_db, array $cfg): void
{
    if (empty($cfg_db)) {
        exit('cfg_db empty');
    }
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

    if (empty($cfg)) {
        exit('cfg empty');
    }
    $err_empty_msg = ' can\'t be empty check config.inc.php';
    $err_nofile_msg = ' file/directory not exists';

    if (empty($cfg['path'])) {
        exit('path' . $err_empty_msg);
    }
    if (!is_dir($cfg['path'])) {
        exit($cfg['path'] . $err_nofile_msg);
    }
}
/**
 *
 * @param array<string,string|int|array<int|string,int|string>> $cfg
 */
function usermode_checks(array $cfg): void
{
    if (empty($cfg)) {
        exit('cfg empty');
    }
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
