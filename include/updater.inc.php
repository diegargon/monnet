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
        $log->info("Update version to 0.31 success");
    }
    if ($db_version < 0.32) {
        $db->query("UPDATE prefs SET pref_value='0.32' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
        $log->info("Update version to 0.32 success");
    }

    if ($db_version < 0.33) {
        $db->query("ALTER TABLE `hosts` CHANGE `warn_msg` `warn_msg` VARCHAR(255) NULL;");
        $db->query("ALTER TABLE `hosts` ADD `token` CHAR(255) NULL AFTER `ports`;");
        $db->query("ALTER TABLE `hosts` ADD `warn_mail` BOOLEAN NOT NULL DEFAULT FALSE AFTER `warn_msg`;");
        $log->info("Update version to 0.33 success");
        $db->query("UPDATE prefs SET pref_value='0.33' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
    }


    //Template
    if ($db_version < 0.00) {
        $db->query("");
        $log->info("Update version to 0.00 success");
        $db->query("UPDATE prefs SET pref_value='0.00' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
    }
}

$query = $db->select('prefs', 'pref_value', ['uid' => 0, 'pref_name' => 'monnet_version']);
$result = $db->fetchAll($query);

$db_version = (float) $result[0]['pref_value'];
$files_version = $cfg['monnet_version'];

if ($files_version > $db_version) {
    trigger_update($log, $db, $db_version, $files_version);
}