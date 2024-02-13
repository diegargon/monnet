<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function trigger_update(Log $log, Database $db, float $db_version, float $files_version) {
    $log->notice("Triggered updater Files: $files_version DB: $db_version");

    if ($db_version < 0.31) {
        $db->query("UPDATE prefs SET pref_value='0.31' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
    }
}

$query = $db->select('prefs', 'pref_value', ['uid' => 0, 'pref_name' => 'monnet_version']);
$result = $db->fetchAll($query);

$db_version = (float) $result[0]['pref_value'];
$files_version = $cfg['monnet_version'];

if ($files_version > $db_version) {
    trigger_update($log, $db, $db_version, $files_version);
}