<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
function trigger_update(Database $db, float $db_version, float $monnet_version): void
{
    Log::notice("Triggered updater Files: $monnet_version DB: $db_version");

    if ($db_version < 0.31) {
        $db->query("UPDATE prefs SET pref_value='0.31' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
        Log::info("Update version to 0.31 successful");
        $db_version = 0.31;
    }
    if ($db_version < 0.32) {
        $db->query("UPDATE prefs SET pref_value='0.32' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
        Log::info("Update version to 0.32 successful");
        $db_version = 0.32;
    }

    if ($db_version < 0.33) {
        $db->query("ALTER TABLE `hosts` CHANGE `warn_msg` `warn_msg` VARCHAR(255) NULL;");
        $db->query("ALTER TABLE `hosts` ADD `token` CHAR(255) NULL AFTER `ports`;");
        $db->query("ALTER TABLE `hosts` ADD `warn_mail` BOOLEAN NOT NULL DEFAULT FALSE AFTER `warn_msg`;");
        $db->query("UPDATE `hosts` SET `system` = '0' WHERE `system` is NULL;");
        $db->query("ALTER TABLE `hosts` CHANGE `system` `system` SMALLINT NOT NULL DEFAULT '0';");
        $db->query("ALTER TABLE `hosts` CHANGE `system` `system_type` SMALLINT NOT NULL DEFAULT '0'; ");
        $db->query("ALTER TABLE `hosts` CHANGE `os` `os` SMALLINT NOT NULL DEFAULT '0';");
        $db->query("ALTER TABLE `hosts` DROP `os_distribution`;");
        $db->query("ALTER TABLE `hosts` ADD `manufacture` SMALLINT NOT NULL DEFAULT '0' AFTER `check_method`;");
        Log::info("Update version to 0.33 successful");
        $db->query("UPDATE prefs SET pref_value='0.33' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
        $db_version = 0.33;
    }

    if ($db_version < 0.34) {
        $db->query("ALTER TABLE `networks` ADD UNIQUE(`network`);");
        $db->query("ALTER TABLE `networks` ADD UNIQUE(`name`);");
        $db->query("ALTER TABLE `networks` CHANGE `network` `network` CHAR(18) CHARACTER "
            . "SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL;");
        $db->query("ALTER TABLE `networks` CHANGE `name` `name` CHAR(255) CHARACTER "
            . "SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL;");
        $db->query("INSERT INTO `categories` (`id`, `cat_type`, `cat_name`, `on`, `disable`, `weight`)"
            . " VALUES ('10', '1', 'L_PRINTERS', '1', '0', '0'); ");
        $db->query("ALTER TABLE `hosts` ADD `online_change` "
            . "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `online`;");
        $db->query("ALTER TABLE `categories` CHANGE `cat_name` `cat_name` CHAR(32) CHARACTER "
            . "SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL;");
        $db->query("ALTER TABLE `hosts_logs` CHANGE `msg` `msg` CHAR(255) CHARACTER "
            . "SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL; ");
        $db->query("ALTER TABLE `system_logs` CHANGE `msg` `msg` CHAR(255) CHARACTER "
            . "SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL; ");
        $db->query("ALTER TABLE `users` CHANGE `timezone` `timezone` CHAR(32) CHARACTER "
            . "SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL; ");

        Log::info("Update version to 0.34 successful");
        $db->query("UPDATE prefs SET pref_value='0.34' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
        $db_version = 0.34;
    }

    //0.35
    if ($db_version < 0.35) {
        $db->query("INSERT INTO `prefs` (`id`, `uid`, `pref_name`, `pref_value`) "
            . "VALUES (11, 0, 'discovery_last_run', '0');");
        $db->query("ALTER TABLE `hosts` DROP `timeout`;");
        $db->query("ALTER TABLE `hosts` ADD `alert_msg` CHAR(255) NULL DEFAULT NULL AFTER `warn_mail`;");
        $db->query("ALTER TABLE `hosts` ADD `alert` TINYINT NOT NULL DEFAULT '0' AFTER `warn_mail`;");
        $db->query("ALTER TABLE `hosts` ADD `misc` JSON NULL DEFAULT NULL");
        $db->query("ALTER TABLE `hosts` ADD `encrypted` TEXT NULL DEFAULT NULL AFTER `notes_id`;");
        $db->query("ALTER TABLE `hosts` CHANGE `ports` `ports` JSON NULL DEFAULT NULL; ");
        $db->query("ALTER TABLE `hosts` CHANGE `warn_msg` `warn_msg` CHAR(255) NULL;");
        $db->query("ALTER TABLE `users` ADD `lang` CHAR(12) NULL DEFAULT NULL AFTER `timezone`;");
        $db->query("ALTER TABLE `users` ADD `theme` CHAR(12) NULL DEFAULT NULL AFTER `timezone`;");
        $db->query("ALTER TABLE `items` ADD `uid` INT NOT NULL DEFAULT '0' AFTER `id`;");
        $db->query("ALTER TABLE `items` ADD `online` INT NOT NULL DEFAULT '0';");
        $db->query("ALTER TABLE `items` ADD `relate_to_host` INT NOT NULL DEFAULT '0';");
        $db->query("ALTER TABLE `notes` ADD `uid` INT NOT NULL DEFAULT '0' AFTER `id`;");
        $db->query("ALTER TABLE `hosts` ADD `scan` TINYINT NOT NULL DEFAULT '0' AFTER `warn_mail`;");
        $db->query("ALTER TABLE `networks` ADD `weight` TINYINT NOT NULL DEFAULT '50' AFTER `scan`;");
        $db->query("ALTER TABLE `categories` DROP `on`;");
        $db->query("ALTER TABLE `items` CHANGE `cat_id` `cat_id` INT NOT NULL DEFAULT '50';");
        $db->query("UPDATE `items` SET `uid` = '1' WHERE `items`.`type` = 'bookmarks';");
        $db->query("UPDATE `notes` SET `uid` = '1';");
        Log::info("Update version to 0.35 successful");
        $db->query("UPDATE prefs SET pref_value='0.35' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
        $db_version = 0.35;
    }

    //0.36
    if ($db_version < 0.00) {
        $db->query("ALTER TABLE `hosts` DROP `mac_vendor`;");
        $db->query("ALTER TABLE `hosts` DROP `manufacture`;");
        $db->query("ALTER TABLE `hosts` DROP `system_type`;");
        $db->query("ALTER TABLE `hosts` DROP `os`;");
        $db->query("ALTER TABLE `hosts` DROP `codename`;");
        Log::info("Update version to 0.36 successful");
        $db->query("UPDATE prefs SET pref_value='0.36' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
        //$db_version = 0.00;
    }

    //Next Template
    if ($db_version < 0.00) {
        $db->query("");
        Log::info("Update version to 0.00 successful");
        $db->query("UPDATE prefs SET pref_value='0.00' WHERE uid='0' AND pref_name='monnet_version' LIMIT 1");
        //$db_version = 0.00;
    }
}

/**
 * @var array $cfg
 * @var Database $db
 */
if ($db) {
    $query = $db->select('prefs', 'pref_value', ['uid' => 0, 'pref_name' => 'monnet_version']);
    $result = $db->fetchAll($query);
    if ($result) {
        $db_version = (float) $result[0]['pref_value'];
        $monnet_version = $cfg['monnet_version'];

        if ($monnet_version > $db_version) {
            trigger_update($db, $db_version, $monnet_version);
        }
    }
}
