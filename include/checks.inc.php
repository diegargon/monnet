<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
use App\Core\Config;

!defined('IN_WEB') ? exit : true;

/**
 *
 * @param array<string,string> $cfg_db
 * @param array<string, mixed> $cfg
 * @return void
 */
function common_checks(array $cfg_db, array $cfg): void
{
    if (empty($cfg_db)) :
        exit('cfg_db empty');
    endif;
    $err_empty_msg = ' can\'t be empty check /etc/monnet/config.db.json';

    if (empty($cfg_db['dbtype'])) :
        exit('dbtype' . $err_empty_msg);
    endif;

    if (empty($cfg_db['dbhost'])) :
        exit('dbhost' . $err_empty_msg);
    endif;

    if (empty($cfg_db['dbname'])) :
        exit('dbname' . $err_empty_msg);
    endif;

    if (empty($cfg_db['dbuser'])) :
        exit('dbuser' . $err_empty_msg);
    endif;

    if (empty($cfg_db['dbpassword'])) :
        exit('dbpassword' . $err_empty_msg);
    endif;
    if (empty($cfg_db['dbcharset'])) :
        exit('dbcharset' . $err_empty_msg);
    endif;

    if (empty($cfg)) :
        exit('cfg empty');
    endif;
}
