<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function trigger_update(Config $ncfg, Database $db, float $db_version, float $files_version): void
{
    Log::notice("Triggered updater File version: $files_version DB version: $db_version");

    // 0.44
    if ($db_version < 0.44) {
        try {
            $ncfg->set('db_monnet_version', 0.44, 1);
            // DONE Poder marcar network como pool
            $db->query("ALTER TABLE `networks` ADD `pool` TINYINT NOT NULL DEFAULT '0' AFTER `scan`;");
            //DONE log_type para guarda diferentes tipos de logs referentes a host, events, alerts etch
            $db->query("
                ALTER TABLE `hosts_logs`
                ADD `log_type` VARCHAR(255) NOT NULL DEFAULT '0'
                COMMENT '0 default, 1 event'
                AFTER `level`;
            ");
            // DONE Drop wrong UNIQUE index date  y crear un index normal
            $db->query("
                ALTER TABLE `stats`
                    DROP INDEX `date`;
            ");
            $db->query("
                ALTER TABLE `stats`
                  ADD INDEX `idx_host_date` (`host_id`, `date`);
            ");
            // DONE No la necesitamos utilizamos stats
            $db->query("
                DROP TABLE IF EXISTS load_stats;
            ");
            // DONE Se usara para guardar tareas referentes a eventos
            $db->query("
                CREATE TABLE IF NOT EXISTS `tasks` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `hid` int NOT NULL,
                  `task` tinyint NOT NULL,
                  `what` varchar(255) NOT NULL,
                  `next_task` int DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB;
            ");
            // DONE Usamos tabla ports en vez hosts->ports
            $db->query("
                CREATE TABLE IF NOT EXISTS `ports` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `hid` int NOT NULL,
                  `scan_type` tinyint NOT NULL DEFAULT '0' COMMENT '0 None 1 remote scan 2 agent provided',
                  `protocol` tinyint NOT NULL COMMENT '1 tcp 2 udp',
                  `pnumber` smallint UNSIGNED NOT NULL,
                  `online` tinyint(1) NOT NULL DEFAULT '0',
                  `interface` varchar(45) DEFAULT NULL,
                  `last_change` datetime NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `idx_hid` (`hid`)
                ) ENGINE=InnoDB;
            ");
            // DONE Utilizamos ncfg y db_monnet_version
            $db->query("START TRANSACTION");
            $db->query("
                DELETE FROM prefs
                WHERE uid = '0' AND pref_name = 'monnet_version'
                LIMIT 1
            ");
            $db->query("COMMIT");
            $db_version = 0.44;
            Log::notice('Update version to 0.44 successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            //$ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, rolling back: ' . $e->getMessage());
        }
    }

   // 0.45 Template
    if ($db_version < 0.45) {
        try {
            $ncfg->set('db_monnet_version', 0.45, 1);
            // DONE CK Review, filtrar y no mostrar logs vistos
            $db->query("
                ALTER TABLE `hosts_logs` ADD `ack` BOOLEAN NOT NULL DEFAULT FALSE AFTER `msg`;
            ");
            // DONE Service Name, el agente los puertos guarda el nombre del servicio
            $db->query("
                ALTER TABLE `ports` ADD `service` VARCHAR(255) NOT NULL AFTER `interface`;
            ");
            // DONE Custom Service name por si el usuario quiere cambiar el nombre a mostrar
            $db->query("
                ALTER TABLE `ports` ADD `custom_service` VARCHAR(255) NULL AFTER `interface`;
            ");
            // DONE el agente envia ip_version ipv4 1 ipv6 2
            $db->query("
                ALTER TABLE `ports` ADD `ip_version` VARCHAR(5) NOT NULL AFTER `interface`;
            ");
            $db_version = 0.45;
            Log::notice('Update version to 0.45 successful');
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.47 Template
    if ($db_version < 0.47) {
        try {
            $ncfg->set('db_monnet_version', 0.47, 1);
            // DONE Guardar reports json como los de ansible
            $db->query("
                CREATE TABLE IF NOT EXISTS `reports` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `host_id` int NOT NULL,
                  `rtype` tinyint NOT NULL,
                  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `report` json NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `idx_host_id_id` (`host_id`, `id`)
                ) ENGINE=InnoDB
            ");
            // DONE Usar glow en vez de online_change
            $db->query("ALTER TABLE `hosts` ADD `glow` "
            . "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `online`;");
            $db->query("ALTER TABLE hosts MODIFY COLUMN mac CHAR(17) DEFAULT NULL;");
            $db->query("ALTER TABLE hosts DROP COLUMN version;");
            // DONE si 1 los host de esa red no se mostraran si esta off
            $db->query("ALTER TABLE networks ADD COLUMN only_online TINYINT(1) NOT NULL DEFAULT 0;");
            $db->query("START TRANSACTION");
            // DONE Permitir configurar una url externa para el agente
            $db->query("
                INSERT INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('agent_external_host', null, 0, 103, NULL, 0),
                ('agent_default_interval', JSON_QUOTE('30'), 1, 103, NULL, 0);
            ");
            $db->query("COMMIT");
            $db_version = 0.47;
            Log::notice('Update version to 0.47 successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

  // 0.48 Template
    if ($db_version < 0.48) {
        try {
            $ncfg->set('db_monnet_version', 0.48, 1);
            $db->query("START TRANSACTION");
            $db->query("DELETE FROM `config` WHERE `ckey` IN ('discover_last_run', 'discoveery_last_run');");
            $db->query("INSERT INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('discovery_last_run', JSON_QUOTE('0'), 1, 0, NULL, 0)");
            $db->query("COMMIT");
            $db_version = 0.48;
            Log::notice('Update version to 0.48 successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

  // 0.49 Template
    if ($db_version < 0.49) {
        try {
            $ncfg->set('db_monnet_version', 0.49, 1);
            $db->query("START TRANSACTION");
            // DONE Adjust type
            $db->query("UPDATE `config` SET `ctype` = '0' WHERE `ckey` = 'discovery_last_run';");
            $db->query("UPDATE `config` SET `ctype` = '0' WHERE `ckey` = 'cli_last_run';");
            // DONE clean system_prefs now in Config
            $db->query("DELETE FROM `prefs` WHERE `uid` = 0;");
            $db->query("COMMIT");
            $db_version = 0.49;
            Log::notice('Update version to 0.49 successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }


    // 0.50 Template
    $update = 0.50;
    if ($db_version < $update) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            // DONE DROP columnas que no necesitamos
            $db->query("ALTER TABLE hosts DROP COLUMN alert_msg;");
            $db->query("ALTER TABLE hosts DROP COLUMN warn_msg;");
            $db->query("ALTER TABLE hosts DROP COLUMN warn_port;");
            $db->query("ALTER TABLE hosts DROP COLUMN ports;");
            $db->query("ALTER TABLE tasks DROP COLUMN what;");
            $db->query("ALTER TABLE tasks DROP COLUMN task;");

            // DONE source_id: uid if rtype=manual task_id if rtype=task
            $db->query("
                ALTER TABLE `reports` ADD `source_id` INT DEFAULT '0' AFTER `host_id`;
            ");
            // Permitir deshabilitar la tarea
            $db->query("
                ALTER TABLE `tasks` ADD `disable` TINYINT(1) DEFAULT '0' AFTER `next_task`;
            ");
            // Nombre de la tarea
            $db->query("
                ALTER TABLE `tasks` ADD `task_name` VARCHAR(100) NOT NULL AFTER `hid`;
            ");
            // Id del tipo de triger config.priv
            $db->query("
                ALTER TABLE `tasks` ADD `trigger_type` SMALLINT NOT NULL AFTER `hid`;
            ");
            // Id del playbook a ejecutar config.priv
            $db->query("
                ALTER TABLE `tasks` ADD `pb_id` SMALLINT NOT NULL AFTER `hid`;
            ");
            // Ultima vez que se ejecuto
            $db->query("
                ALTER TABLE `tasks` ADD `last_triggered` DATETIME NULL AFTER `trigger_type`;
            ");
            // DONE Guarda el event type
            $db->query("
                ALTER TABLE `hosts_logs` ADD `event_type` SMALLINT DEFAULT '0' AFTER `log_type`;
            ");
            $db->query("START TRANSACTION");
            $db->query("COMMIT");
            $db_version = $update;
            Log::notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    $update = 0.51;
    if ($db_version < $update) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            $db->query("START TRANSACTION");
            // DONE playbook id for the report
            $db->query("ALTER TABLE `reports` ADD `pb_id` INT NOT NULL AFTER `host_id`;");
            $db->query("COMMIT");
            $db_version = $update;
            Log::notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    $update = 0.52;
    if ($db_version < $update) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            /* Usado para extra_vars u otros en JSON format */
            $db->query("ALTER TABLE `tasks` ADD `extra` JSON NULL DEFAULT NULL");
            /* Task scheduler */
            $db->query("ALTER TABLE `tasks` ADD `task_interval` VARCHAR(10) DEFAULT NULL");
            $db->query("ALTER TABLE `tasks` ADD `interval_seconds` INT DEFAULT NULL");
            $db->query("ALTER TABLE `tasks` ADD `next_trigger` DATETIME NULL AFTER `last_triggered`;");
            $db->query("ALTER TABLE `tasks` ADD `created` DATETIME DEFAULT CURRENT_TIMESTAMP;");
            $db_version = $update;
            Log::notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    $update = 0.53;
    if ($db_version < $update) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            /* DONE Move to row for easy select on db */
            $db->query("ALTER TABLE `hosts` ADD `agent_installed` TINYINT(1) NOT NULL DEFAULT 0;");
            $db_version = $update;
            Log::notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    $update = 0.54;
    if ($db_version < $update) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            //$db->query("
            //");
            $db->query("
                CREATE TABLE IF NOT EXISTS `ansible_vars` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `hid` int NOT NULL,
                  `vtype` tinyint NOT NULL,
                  `vkey` varchar(255) NOT NULL,
                  `vvalue` varchar(700) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB;
            ");
            $db->query("
                INSERT INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('public_key', 'null', 10, 10, NULL, 0);
            ");
            $db_version = $update;
            Log::notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }
    // 0.55
    $update = 0.55;
    if ($db_version < $update) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            $db->query("ALTER TABLE `tasks` ADD `event_id` INT DEFAULT 0");
            $db->query("ALTER TABLE `tasks` ADD `crontime` VARCHAR(255)");
            $db->query("ALTER TABLE `tasks` ADD `groups` VARCHAR(255)");
            $db->query("ALTER TABLE `ports` CHANGE `ip_version` `ip_version` VARCHAR(5) NULL DEFAULT NULL;");
            $db_version = $update;
            Log::notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }
    // 0.56
    $update = 0.56;
    if ($db_version < $update) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            # Unused
            $result = $db->query("SHOW COLUMNS FROM `hosts` LIKE 'access_results'");
            if ($result && $result->num_rows > 0) {
                $db->query("ALTER TABLE `hosts` DROP COLUMN `access_results`");
            }
            $result = $db->query("SHOW COLUMNS FROM `hosts` LIKE 'fingerprint'");
            if ($result && $result->num_rows > 0) {
                $db->query("ALTER TABLE `hosts` DROP COLUMN `fingerprint`");
            }
            $result = $db->query("SHOW COLUMNS FROM `hosts` LIKE 'latency'");
            if ($result && $result->num_rows > 0) {
                $db->query("ALTER TABLE `hosts` DROP COLUMN `latency`");
            }
            $db->query("START TRANSACTION");
            # Migration to ncfg
            $db->query("
                INSERT IGNORE INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('log_level', JSON_QUOTE('5'), 1, 105, NULL, 0),
                ('log_file', JSON_QUOTE('logs/monnet.log'), 0, 105, NULL, 0),
                ('system_log_to_syslog', JSON_QUOTE('0'), 2, 105, NULL, 0),
                ('system_log_to_db', JSON_QUOTE('1'), 2, 105, NULL, 0),
                ('system_log_to_db_debug', JSON_QUOTE('0'), 2, 105, NULL, 0),
                ('log_to_file', JSON_QUOTE('1'), 2, 105, NULL, 0),
                ('log_file_owner', JSON_QUOTE('www-data'), 0, 105, NULL, 0),
                ('log_file_owner_group', JSON_QUOTE('www-data'), 0, 105, NULL, 0),
                ('term_hosts_log_level', JSON_QUOTE('5'), 1, 105, NULL, 0),
                ('term_system_log_level', JSON_QUOTE('5'), 1, 105, NULL, 0),
                ('term_max_lines', JSON_QUOTE('100'), 1, 105, NULL, 0),
                ('term_show_system_logs', JSON_QUOTE('1'), 2, 105, NULL, 0),
                ('theme_css', JSON_QUOTE('default'), 0, 2, NULL, 0),
                ('theme', JSON_QUOTE('default'), 0, 2, NULL, 0),
                ('refresher_time', JSON_QUOTE('2'), 1, 2, NULL, 0),
                ('glow_time', JSON_QUOTE('10'), 1, 2, NULL, 0),
                ('port_timeout_local', JSON_QUOTE('0.5'), 3, 106, NULL, 0),
                ('port_timeout', JSON_QUOTE('0.8'), 3, 106, NULL, 0),
                ('ping_nets_timeout', JSON_QUOTE('200000'), 1, 106, NULL, 0),
                ('ping_hosts_timeout', JSON_QUOTE('400000'), 1, 106, NULL, 0),
                ('ping_local_hosts_timeout', JSON_QUOTE('300000'), 1, 106, NULL, 0),
                ('clear_logs_intvl', JSON_QUOTE('30'), 1, 104, NULL, 0),
                ('clear_stats_intvl', JSON_QUOTE('15'), 1, 104, NULL, 0),
                ('clear_reports_intvl', JSON_QUOTE('30'), 1, 104, NULL, 0),
                ('agent_allow_selfcerts', JSON_QUOTE('1'), 2, 103, NULL, 0),
                ('default_mem_alert_threshold', JSON_QUOTE('90'), 1, 103, NULL, 0),
                ('default_mem_warn_threshold', JSON_QUOTE('80'), 1, 103, NULL, 0),
                ('default_disks_alert_threshold', JSON_QUOTE('90'), 1, 103, NULL, 0),
                ('default_disks_warn_threshold', JSON_QUOTE('80'), 1, 103, NULL, 0);
            ");

            //$db->query("
            //");
            $db->query("COMMIT");
            $db_version = $update;
            Log::notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.57
    $update = 0.57;
    if ($db_version < $update) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            $db->query("ALTER TABLE `ports` ADD `latency` FLOAT DEFAULT NULL");
            $db->query("ALTER TABLE `ports` ADD `last_check` datetime DEFAULT NULL");
            $db->query("ALTER TABLE `ports` MODIFY `scan_type` tinyint");
            $db->query("ALTER TABLE `ports` MODIFY `service` varchar(255) DEFAULT NULL");
            $db->query("START TRANSACTION");
            $db->query("
                INSERT IGNORE INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('term_date_format', JSON_QUOTE('[d][H:i]'), 0, 5, NULL, 0),
                ('date_format', JSON_QUOTE('d-m-Y'), 0, 5, NULL, 0),
                ('time_format', JSON_QUOTE('H:i:s'), 0, 5, NULL, 0),
                ('datetime_format', JSON_QUOTE('d-m-Y H:i:s'), 0, 5, NULL, 0),
                ('datetime_format_min', JSON_QUOTE('d/H:i'), 0, 5, NULL, 0),
                ('datatime_graph_format', JSON_QUOTE('H:i'), 0, 5, NULL, 0),
                ('datetime_log_format', JSON_QUOTE('d-m-y H:i:s'), 0, 5, NULL, 0),
                ('default_charset', JSON_QUOTE('utf-8'), 0, 1, NULL, 0),
                ('default_timezone', JSON_QUOTE('UTC'), 0, 1, NULL, 0),
                ('graph_charset', JSON_QUOTE('es-ES'), 0, 1, NULL, 0),
                ('web_title', JSON_QUOTE('MonNet'), 0, 2, NULL, 0),
                ('check_retries_usleep', JSON_QUOTE('500000'), 1, 106, NULL, 0),
                ('check_retries', JSON_QUOTE('4'), 1, 106, NULL, 0);
            ");
            $db->query("COMMIT");
            $db_version = $update;
            Log::notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // Template
    $update = 0.00;
    if ($db_version < $update) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            //$db->query("
            //");
            $db->query("START TRANSACTION");
            //$db->query("
            //");
            $db->query("COMMIT");
            $db_version = $update;
            Log::notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }
}

/**
 * @var Config $ncfg
 * @var Database $db
 */
if (!$db->isConn()) {
    echo "No Dabase Connection Error";
}
$lockFile = '/tmp/monnet_update.lock';
$db_version = (float) $ncfg->get('db_monnet_version');

if ($db_version) {
    $files_version = (float) $ncfg->get('monnet_version');

    if (($files_version > $db_version) && !file_exists($lockFile)) {
        if (file_put_contents($lockFile, 'locked') !== false) {
            Log::notice('Triggered Update');
            trigger_update($ncfg, $db, $db_version, $files_version);
            unlink($lockFile);
        } else {
            Log::error("Could not create lock file: $lockFile");
        }
    }
}
