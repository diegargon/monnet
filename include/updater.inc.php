<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
use App\Core\DBManager;
use App\Core\AppContext;
use App\Core\ConfigService;
use App\Services\LogSystemService;

!defined('IN_WEB') ? exit : true;

$logSys = new LogSystemService($ctx);

function trigger_update(ConfigService $ncfg, DBManager $db, float $db_version, float $files_version): void
{
    global $logSys;
    $logSys->notice("Triggered updater File version: $files_version DB version: $db_version");

    // 0.64 DONE
    $update = 0.64;
    if ($db_version == 0.63) {
        try {
            /* DONE change pb_id to allow null to use pid */
            $db->query("ALTER TABLE reports ADD COLUMN pid VARCHAR(255) AFTER host_id;");
            $db->query("ALTER TABLE tasks MODIFY pb_id INT NULL;");
            foreach ($ncfg->get('playbooks') as $playbook) {
                $pbId = (int)$playbook['id'];
                $pname = $playbook['name'];

                $db->query("UPDATE reports SET pid = '$pname' WHERE pb_id = $pbId");
            }
            // DONE Option to implemente clear offline hosts if this options is active 0/1
            // DONE changed to clean. GW must do a task host clean
            $db->query("ALTER TABLE `networks` ADD `clear` TINYINT NOT NULL DEFAULT '0';");
            $db->query("START TRANSACTION");
            // DONE key was renamed to clean_hosts_days
            $db->query("
                INSERT IGNORE INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('clean_host_days', JSON_QUOTE('30'), 1, 104, NULL, 0)
            ");
            $db->query("COMMIT");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.65 DONE
    $update = 0.65;
    if ($db_version == 0.64) {
        try {
            /* DONE Set unused to allow null  before delete */
            $db->query("ALTER TABLE tasks MODIFY pb_id INT NULL;");
            $db->query("ALTER TABLE reports MODIFY pb_id INT NULL;");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.66 DONE
    $update = 0.66;
    if ($db_version == 0.65) {
        try {
            # DONE Rename fields
            $db->query("ALTER TABLE sessions CHANGE created_at created DATETIME");
            $db->query("ALTER TABLE sessions CHANGE expired_at expire DATETIME");
            $db->query("ALTER TABLE sessions CHANGE last_active_at last_active DATETIME");
            # DONE Modify
            $db->query("ALTER TABLE users MODIFY COLUMN timezone VARCHAR(32);");
            $db->query("ALTER TABLE users MODIFY COLUMN password VARCHAR(255);");
            $db->query("START TRANSACTION");
            # DONE: Option to configure de server_endpoint
            # DONE DISCARDING: agent log level will be string
            $db->query("
                INSERT IGNORE INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('agent_log_level', JSON_QUOTE('5'), 1, 103, NULL, 0),
                ('server_endpoint', JSON_QUOTE('/feedme.php'), 0, 103, NULL, 0)
            ");
            $db->query("COMMIT");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.67 PENDING
    $update = 0.67;
    if ($db_version == 0.66) {
        try {
            # DONE Removed unused
            $db->query("ALTER TABLE hosts DROP COLUMN online_change;");
            $db->query("ALTER TABLE hosts DROP COLUMN last_seen;");
            $db->query("ALTER TABLE hosts DROP COLUMN encrypted;");
            # linked: id hypervisor or other host
            # link vms/containers to their host
            # depends de rol en la tabla no en misc para buscar hypervisors
            $db->query("ALTER TABLE `hosts` ADD `linked` INT NULL DEFAULT 0;");
            $db->query("START TRANSACTION");
            # DONE Option mark view report
            $db->query("ALTER TABLE reports ADD COLUMN ack TINYINT NOT NULL DEFAULT '0';");
            # DONE Option mark the status of the task 0 success 1 failed
            $db->query("ALTER TABLE reports ADD COLUMN status TINYINT NOT NULL DEFAULT '0';");
            //$db->query("
            //");
            # DONE sid expire remove from config.priv
            # DONE agent_internal_host gw must use if set
            $db->query("
                INSERT IGNORE INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('agent_internal_host', JSON_QUOTE(''), 0, 103, NULL, 0),
                ('sid_expire', JSON_QUOTE('604.800'), 1, 10, NULL, 0)
            ");
            $db->query("COMMIT");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.68 PENDING
    $update = 0.68;
    if ($db_version == 0.67) {
        try {
            # DONE Remove Unused
            $db->query("ALTER TABLE reports DROP COLUMN pb_id;");
            $db->query("ALTER TABLE tasks DROP COLUMN pb_id;");
            $db->query("ALTER TABLE tasks DROP COLUMN extra;");
            # PENDING system_type to rol
            $db->query("ALTER TABLE `hosts` ADD `rol` INT NULL DEFAULT 0;");
            # DONE Counter for other task and disable uniq tasks
            $db->query("ALTER TABLE `tasks` ADD `done` INT NULL DEFAULT 0;");
            # DONE Renaming
            $db->query("UPDATE `config`
                SET `ckey` = 'clean_hosts_days'
                WHERE `ckey` = 'clean_host_days'
            ");
            # DONE Gw Intervals
            $db->query("
                INSERT IGNORE INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('gw_send_logs_intvl', JSON_QUOTE('20'), 1, 4, NULL, 0),
                ('gw_discover_host_intvl', JSON_QUOTE('130'), 1, 4, NULL, 0),
                ('gw_host_checker_intvl', JSON_QUOTE('300'), 1, 4, NULL, 0),
                ('gw_prune_intvl', JSON_QUOTE('86400'), 1, 4, NULL, 0),
                ('gw_ansible_tasks_intvl', JSON_QUOTE('60'), 1, 4, NULL, 0),
                ('agent_internal_host', JSON_QUOTE(''), 0, 103, NULL, 0)
            ");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.69 DONE Update Test
    $update = 0.69;
    if ($db_version == 0.68) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }
    // 0.70 DONE Update Test
    $update = 0.70;
    if ($db_version == 0.69) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.71 DONE
    $update = 0.71;
    if ($db_version == 0.70) {
        try {
            $db->query("START TRANSACTION");
            $db->query("UPDATE `config`
                SET `cvalue` = JSON_QUOTE('info')
                WHERE `ckey` = 'agent_log_level'
            ");
            $db->query("UPDATE `config`
                SET `ctype` = 0
                WHERE `ckey` = 'agent_log_level'
            ");
            $db->query("UPDATE `config`
                SET `cvalue` = JSON_QUOTE('1320')
                WHERE `ckey` = 'gw_discover_host_intvl'
            ");
            $db->query("COMMIT");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.72 DONE
    $update = 0.72;
    if ($db_version == 0.71) {
        try {
            # DONE Mark task success/fail
            $db->query("ALTER TABLE `tasks` ADD COLUMN status TINYINT NOT NULL DEFAULT '0';");
            # DONE Clean old host moved from clear G: Change M: Implement
            $db->query("ALTER TABLE `networks` ADD `clean` TINYINT NOT NULL DEFAULT '0';");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.73 DONE
    $update = 0.73;
    if ($db_version == 0.72) {
        try {
            # DONE tid renamed to reference
            $db->query("ALTER TABLE `hosts_logs` ADD `tid` INT DEFAULT '0';");
            # DONE Gateway: Marcar cuando online
            $db->query("ALTER TABLE `hosts` ADD `last_seen` DATETIME DEFAULT NULL");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            Log::error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.74 DONE
    $update = 0.74;
    if ($db_version == 0.73) {
        try {
            # For renaming fields
            # DONE clear_done_tasks for clean uniq tasks done
            # DONE clear not seen interval for purge host on clean networks
            $db->query("START TRANSACTION");
            $db->query("UPDATE `config` SET `cvalue` = JSON_QUOTE('604800') WHERE `ckey` = 'sid_expire'");
            $db->query("
                INSERT IGNORE INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('clear_not_seen_hosts_intvl', JSON_QUOTE('30'), 1, 104, NULL, 0),
                ('clear_task_done_intvl', JSON_QUOTE('30'), 1, 104, NULL, 0)
            ");
            $db->query("COMMIT");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.75 DONE
    $update = 0.75;
    if ($db_version == 0.74) {
        try {
            $db->query("START TRANSACTION");
            # DONE already done, need update
            $now = gmdate('Y-m-d H:i:s');
            $db->query("
                INSERT IGNORE INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('last_send_logs', JSON_QUOTE('$now'), 4, 0, NULL, 0),
                ('last_discovery_hosts', JSON_QUOTE('$now'), 4, 0, NULL, 0),
                ('last_hosts_checker', JSON_QUOTE('$now'), 4, 0, NULL, 0),
                ('last_ansible_task', JSON_QUOTE('$now'), 4, 0, NULL, 0),
                ('last_prune', JSON_QUOTE('$now'), 4, 0, NULL, 0),
                ('last_weekly_task', JSON_QUOTE('$now'), 4, 0, NULL, 0),
                ('default_lang', JSON_QUOTE('es'), 0, 1, NULL, 0)
            ");
            $ncfg->set('db_monnet_version', $update, 1);
            $db->query("COMMIT");
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.76 DONE
    $update = 0.76;
    if ($db_version == 0.75) {
        try {
            # DONE
            $db->query("ALTER TABLE `users` ADD `updated` DATETIME DEFAULT NULL");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.77 DONE Error Solving update
    $update = 0.77;
    if ($db_version == 0.76) {
        try {
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $db->query("COMMIT");
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.78 DONE
    $update = 0.78;
    if ($db_version == 0.77) {
        try {
            $db->query("ALTER TABLE `config` MODIFY `ccat` INT(11) NOT NULL DEFAULT 0");
            $db->query("START TRANSACTION");
            # DONE: Module weather_widget config keys
            $db->query("
                INSERT IGNORE INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('weather_country', JSON_QUOTE('vigo'), 0, 10000, NULL, 0),
                ('weather_api', JSON_QUOTE('89fe8d3a8486486fc682ba97dc28850f'), 0, 10000, NULL, 0)
            ");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $db->query("COMMIT");
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.79 DONE
    $update = 0.79;
    if ($db_version == 0.78) {
        try {
            # DONE Use ref instad of tid
            $db->query("ALTER TABLE `hosts_logs` DROP COLUMN `tid`;");
            $db->query("ALTER TABLE `hosts_logs` ADD COLUMN `reference` VARCHAR(255) DEFAULT NULL;");
            # DONE To delete After change name in use to clean in G
            $db->query("ALTER TABLE `networks` DROP COLUMN clear;");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.80 DONE
    $update = 0.80;
    if ($db_version == 0.79) {
        try {
            # DONE Use linkable to mark hosts that can be linked
            $db->query("ALTER TABLE `hosts` ADD COLUMN `linkable` tinyint(1) DEFAULT 0;");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }
    // 0.81 DONE
    $update = 0.81;
    if ($db_version == 0.80) {
        try {
            # DONE 0 checked, 1 must check
            $db->query("ALTER TABLE `hosts` ADD COLUMN `mac_check` tinyint(1) DEFAULT 0;");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // 0.82 DONE /SQL
    $update = 0.82;
    if ($db_version == 0.81) {
        $now = gmdate('Y-m-d H:i:s');
        try {
            $db->query("START TRANSACTION");
            $db->query("
                INSERT IGNORE INTO `config` (`ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
                ('last_hourly_task', JSON_QUOTE('$now'), 4, 0, NULL, 0)
            ");
            $db->query("COMMIT");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // Later
    $update = 0.00;
    if ($db_version == 0.00) {
        try {
            # Mole Agent is used as a gateway to stealh discovery scan in network, perhaps ping check
            $db->query("ALTER TABLE `hosts` ADD COLUMN `mole` tinyint(1) DEFAULT 0;");
            # Set Playbooks Global Variable
            $db->query("ALTER TABLE `ansible_vars` ADD `global` TINYINT NOT NULL DEFAULT '0';");
            # User date formet
            $db->query("ALTER TABLE `users` ADD `dateformat` VARCHAR(20) NULL;");
            # User rols
            $db->query("ALTER TABLE `users` ADD `rol` INT NULL DEFAULT 0;");
            $db->query("START TRANSACTION");
            # Unsed keyword
            $db->query("DELETE FROM `config` WHERE `ckey` = 'clean_hosts_days'");
            //$db->query("
            //");
            $db->query("COMMIT");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }

    // Template
    $update = 0.00;
    if ($db_version == 0.00) {
        try {
            //$db->query("
            //");
            $db->query("START TRANSACTION");
            //$db->query("
            //");
            $db->query("COMMIT");
            $ncfg->set('db_monnet_version', $update, 1);
            $db_version = $update;
            $logSys->notice("Update version to $update successful");
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $ncfg->set('db_monnet_version', $db_version, 1);
            $logSys->error('Transaction failed, trying rolling back: ' . $e->getMessage());
        }
    }
}

/**
 * @var ConfigService $ncfg
 * @var DBManager $db
 */
if (!$db->isConnected()) {
    echo "No Database Connection Error";
    exit();
}
$lockFile = '/tmp/monnet_update.lock';
$db_version = (float) $ncfg->get('db_monnet_version');
$files_version = (float) $ncfg->get('monnet_version');
$maxLockTime = 120;

if ($db_version && ($files_version > $db_version)) {
        $trigger = true;

        $fp = fopen($lockFile, 'w+');
        if (!$fp) {
            $logSys->error("Can not lock for update");
            $trigger = false;
        }

        if ($trigger && !flock($fp, LOCK_EX | LOCK_NB)) {
            $lockTime = filemtime($lockFile);
            if (time() - $lockTime > $maxLockTime) {
                $logSys->warning("Removing old updater lock");
                fclose($fp);
                unlink($lockFile);
                $trigger = true;
            } else {
                $logSys->info("Updating already start");
                fclose($fp);
                $trigger = false;
            }
        }

        if ($trigger) {
            try {
                $logSys->notice('Triggered Update '. $files_version);
                trigger_update($ncfg, $db, $db_version, $files_version);
            } catch (Throwable $e) {
                $logSys->error('Update failed: ' . $e->getMessage());
            } finally {
                if (isset($fp) && is_resource($fp)) {
                    flock($fp, LOCK_UN);
                    fclose($fp);
                }
                @unlink($lockFile);
            }
        }
}
