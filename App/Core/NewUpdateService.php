<?php

namespace App\Core;

use App\Core\ConfigService;
use App\Services\LogSystemService;
use App\Core\AppContext;


use DBManager;
use Exception;
use Throwable;

class UpdateService
{
    private $logSys;

    public function __construct(AppContext $ctx)
    {
        $this->logSys = new \App\Services\LogSystemService($ctx);
    }

    public function runUpdates(Config $ncfg, Database $db)
    {
        if (!$db->isConn()) {
            echo "No Dabase Connection Error";
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
                $this->logSys->error("Can not lock for update");
                $trigger = false;
            }

            if ($trigger && !flock($fp, LOCK_EX | LOCK_NB)) {
                $lockTime = filemtime($lockFile);
                if (time() - $lockTime > $maxLockTime) {
                    $this->logSys->warning("Removing old updater lock");
                    fclose($fp);
                    unlink($lockFile);
                    $trigger = true;
                } else {
                    $this->logSys->info("Updating already start");
                    fclose($fp);
                    $trigger = false;
                }
            }

            if ($trigger) {
                try {
                    $this->logSys->notice('Triggered Update '. $files_version);
                    $this->triggerUpdate($ncfg, $db, $db_version, $files_version);
                } catch (Throwable $e) {
                    $this->logSys->error('Update failed: ' . $e->getMessage());
                } finally {
                    if (isset($fp) && is_resource($fp)) {
                        flock($fp, LOCK_UN);
                        fclose($fp);
                    }
                    @unlink($lockFile);
                }
            }
        }
    }

    private function triggerUpdate(Config $ncfg, Database $db, float $db_version, float $files_version): void
    {

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
}