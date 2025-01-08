<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
function trigger_update(Config $ncfg, Database $db, float $db_version, float $files_version): void
{
    Log::notice("Triggered updater File version: $files_version DB version: $db_version");

    // 0.43
    if ($db_version < 0.43) {
        try {
            $db->query("START TRANSACTION");
            $ncfg->set('db_monnet_version', 0.43, 1);
            $db->query("COMMIT");
            $db_version = $files_version;
            Log::info('Update version to ' . $files_version . ' successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::err('Transaction failed, rolling back: ' . $e->getMessage());
        }
    }
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
            //DONE Drop wrong UNIQUE index date  y crear un index normal
            $db->query("
                ALTER TABLE `stats`
                    DROP INDEX `date`;
            ");
            $db->query("
                ALTER TABLE `stats`
                  ADD INDEX `idx_host_date` (`host_id`, `date`);
            ");
            //DONE No la necesitamos utilizamos stats
            $db->query("
                DROP TABLE IF EXISTS load_stats;
            ");
            // Se usara para guardar tareas referentes a eventos
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
            //DONE Usamos tabla ports en vez hosts->ports
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
            //DONE Utilizamos ncfg y db_monnet_version
            $db->query("START TRANSACTION");
            $db->query("
                DELETE FROM prefs
                WHERE uid = '0' AND pref_name = 'monnet_version'
                LIMIT 1
            ");
            $db->query("COMMIT");
            $db_version = $files_version;
            Log::info('Update version to ' . $files_version . ' successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            //$ncfg->set('db_monnet_version', $db_version, 1);
            Log::err('Transaction failed, rolling back: ' . $e->getMessage());
        }
    }

   // 0.45 Template
    if ($db_version < 0.45) {
        try {
            $ncfg->set('db_monnet_version', 0.45, 1);
            //DONE CK Review, filtrar y no mostrar logs vistos
            $db->query("
                ALTER TABLE `hosts_logs` ADD `ack` BOOLEAN NOT NULL DEFAULT FALSE AFTER `msg`;
            ");
            //DONE Service Name, el agente los puertos guarda el nombre del servicio
            $db->query("
                ALTER TABLE `ports` ADD `service` VARCHAR(255) NOT NULL AFTER `interface`;
            ");
            //Custom Service name por si el usuario quiere cambiar el nombre a mostrar
            $db->query("
                ALTER TABLE `ports` ADD `custom_service` VARCHAR(255) NULL AFTER `interface`;
            ");
            //DONE el agente envia ip_version ipv4 1 ipv6 2
            $db->query("
                ALTER TABLE `ports` ADD `ip_version` VARCHAR(5) NOT NULL AFTER `interface`;
            ");
            $db->query("START TRANSACTION");
            //$db->query("
            //");
            $db->query("COMMIT");
            $db_version = $files_version;
            Log::info('Update version to ' . $files_version . ' successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::err('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.46 do nothing
    if ($db_version < 0.46) {
        try {
            $ncfg->set('db_monnet_version', 0.46, 1);
            $db_version = $files_version;
            Log::info('Update version to ' . $files_version . ' successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::err('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.47 Template
    if ($db_version < 0.47) {
        try {
            $ncfg->set('db_monnet_version', 0.47, 1);
            // Guardar reports json como los de ansible
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
            // Usar glow en vez de online_change
            // Borrar online_change
            $db->query("ALTER TABLE `hosts` ADD `glow` "
            . "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `online`;");
            $db->query("ALTER TABLE hosts MODIFY COLUMN mac CHAR(17) DEFAULT NULL;");
            $db->query("ALTER TABLE hosts DROP COLUMN version;");
            //si 1 los host de esa red no se mostraran si esta off
            $db->query("ALTER TABLE networks ADD COLUMN only_online TINYINT(1) NOT NULL DEFAULT 0;");
            $db->query("START TRANSACTION");
            // Permitir configurar una url externa para el agente
            $db->query("
                INSERT INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('agent_external_host', null, 0, 103, NULL, 0),
                ('agent_default_interval', JSON_QUOTE('30'), 1, 103, NULL, 0);
            ");
            $db->query("COMMIT");
            $db_version = $files_version;
            Log::info('Update version to ' . $files_version . ' successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::err('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

  // 0.48 Template
    if ($db_version < 0.00) {
        try {
            $ncfg->set('db_monnet_version',0.00 , 1);
            $db->query("START TRANSACTION");
            // DROP hosts->alert_msg host->warn_msg $host->warn_port hosts->ports
            // DROP host->online_change
            //$db->query("
            //");
            $db->query("COMMIT");
            $db_version = $files_version;
            Log::info('Update version to ' . $files_version . ' successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::err('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

   // 0.00 Template
    if ($db_version < 0.00) {
        try {
            $ncfg->set('db_monnet_version', 0.00, 1);
            $db->query("START TRANSACTION");
            //$db->query("
            //");
            $db->query("COMMIT");
            $db_version = $files_version;
            Log::info('Update version to ' . $files_version . ' successful');
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            Log::err('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }
}

/**
 * @var array<int|string, mixed> $cfg
 * @var Config $ncfg
 * @var Database $db
 */
if ($db->isConn()) {
    $lockFile = '/tmp/monnet_update.lock';
    //$query = $db->select('prefs', 'pref_value', ['uid' => 0, 'pref_name' => 'monnet_version']);
    $db_version = (float) $ncfg->get('db_monnet_version');
    if ($db_version) :
        $files_version = (float) $cfg['monnet_version'];

        if (($files_version > $db_version) && !file_exists($lockFile)) :
            file_put_contents($lockFile, 'locked');
            Log::info("Triggered");
            trigger_update($ncfg, $db, $db_version, $files_version);
            unlink($lockFile);
        endif;
    endif;
}
