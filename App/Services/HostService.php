<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Models\CmdHostModel;
use App\Models\LogHostsModel;
use App\Models\HostsModel;

class HostService
{
    private CmdHostModel $cmdHostModel;
    private LogHostsModel $logHostsModel;
    private HostFormatter $hostFormatter;
    private AnsibleService $ansibleService;
    private HostsModel $hostsModel;

    private \AppContext $ctx;
    private \Config $ncfg;
    private \DBManager $db;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get('Config');
        $this->db = $ctx->get('DBManager');
        $this->cmdHostModel = new CmdHostModel($this->db);
        $this->logHostsModel = new LogHostsModel($this->db);
        $this->hostsModel = new HostsModel($this->db);

        $this->hostFormatter = new HostFormatter($ctx);
        $this->ansibleService = new AnsibleService($ctx);
    }
    public function __destruct()
    {
        unset($this->ctx, $this->ncfg);
        unset($this->cmdHostModel);
        unset($this->logHostsModel);
        unset($this->hostFormatter);
        unset($this->ansibleService);
        unset($this->logHostsModel);
        unset($this->hostsModel);
    }

    public function getHostById(int $id): array
    {
        $host = $this->cmdHostModel->getHostById($id);

        return $host;
    }
    /**
     *
     * @param int $target_id
     * @return array<string, string|int>
     */
    public function getDetails(int $target_id): array
    {
        $hostDetails = $this->cmdHostModel->getHostById($target_id);

        if (!$hostDetails) {
            return ['error' => 'No details found for the host'];
        }

        if (!empty($hostDetails['misc'])) {
            $hostDetails['misc'] = $this->decodeMisc($hostDetails['misc']);
            /* TODO: Migrate: keep misc values in misc then delete this */
            $hostDetails = array_merge($hostDetails, $hostDetails['misc']);

            if (!isset($hostDetails['misc']['mem_alert_threshold'])) {
                $hostDetails['misc']['mem_alert_threshold'] = $this->ncfg->get('default_mem_alert_threshold');
            }
            if (!isset($hostDetails['misc']['mem_warn_threshold'])) {
                $hostDetails['misc']['mem_warn_threshold'] = $this->ncfg->get('default_mem_warn_threshold');
            }
            if (!isset($hostDetails['misc']['disks_alert_threshold'])) {
                $hostDetails['misc']['disks_alert_threshold'] = $this->ncfg->get('default_disks_alert_threshold');
            }
            if (!isset($hostDetails['misc']['disks_warn_threshold'])) {
                $hostDetails['misc']['disks_warn_threshold'] = $this->ncfg->get('default_disks_warn_threshold');
            }
            /*
            if (!isset($hostDetails['misc']['cpu_alert_threshold'])) {
                $hostDetails['misc']['cpu_alert_threshold'] = $this->ncfg->get('default_cpu_alert_threshold');

            }
            if (!isset($hostDetails['misc']['cpu_warn_threshold'])) {
                $hostDetails['misc']['cpu_warn_threshold'] = $this->ncfg->get('default_cpu_warn_threshold');
            }
            */
        }
        $hostDetails = $this->hostFormatter->format($hostDetails);

        // Get remote  ports (1)
        $hostDetails['remote_ports'] = $this->cmdHostModel->getHostScanPorts($target_id, 1);
        // Agent provided port list (2)
        if ($hostDetails['agent_installed']) {
            $agent_ports = $this->cmdHostModel->getHostScanPorts($target_id, 2);
            //Formatting
            foreach ($agent_ports as $key_port => $port) :
                if (isset($port['interface'])) :
                    if (strpos($port['interface'], '[') === 0) {
                        $agent_ports[$key_port]['class'] = 'port_ipv6';
                        if (strpos($port['interface'], '[::]') === 0) :
                            $agent_ports[$key_port]['class'] .= ' port_local';
                        endif;
                    } elseif (strpos($port['interface'], '127') === 0) {
                        $agent_ports[$key_port]['class'] = 'port_local';
                    }
                endif;
            endforeach;
            $hostDetails['agent_ports']  = $agent_ports;
        }

        return $hostDetails;
    }

    public function getFiltered(array $filters): array
    {
        $hosts = $this->hostsModel->getFiltered($filters);
        foreach ($hosts as &$host) {
            $host['display_name'] = $this->hostFormatter->getDisplayName($host);
            if (!empty($host['misc'])) {
                $host['misc'] = $this->decodeMisc($host['misc']);
            }
        }

        return $hosts;
    }

    /**
     *
     * @param int $hid
     * @return array<string, string|int>
     */
    public function getDetailsStats(int $hid): array
    {
        $hostDetails = $this->cmdHostModel->getMiscById($hid);

        if (!$hostDetails) {
            return ['error' => 'No details found for the host'];
        }

        if (!empty($hostDetails['misc'])) {
            $hostDetails['misc'] = $this->decodeMisc($hostDetails['misc']);
            /* TODO: Migrate: keep misc values in misc then delete this */
            $hostDetails = array_merge($hostDetails, $hostDetails['misc']);
        }

        $this->hostFormatter->formatMisc($hostDetails);

        if (!empty($hostDetails['load_avg'])) {
            $hostDetails_stats['load_avg'] = $hostDetails['load_avg'];
        }
        if (!empty($hostDetails['mem_info'])) {
            $hostDetails_stats['mem_info'] = $hostDetails['mem_info'];
        }
        if (!empty($hostDetails['disks_info'])) {
            $hostDetails_stats['disks_info'] = $hostDetails['disks_info'];
        }
        if (!empty($hostDetails['iowait'])) {
            $hostDetails_stats['iowait'] = $hostDetails['iowait'];
        }

        return $hostDetails_stats;
    }

    /**
     * Status (null All) (0 Off) (1 On) (2 Fail) - Returns hosts
     * @param int|null $status
     * @return list<string, mixed>>
     */
    public function getAnsibleHosts(?int $status = null): array
    {
        $hosts = $this->cmdHostModel->getAnsibleHosts();
        $result_hosts = [];

        if (!$this->ncfg->get('ansible')) :
            return [];
        endif;

        foreach ($hosts as $host) :
            $host['display_name'] = $this->hostFormatter->getDisplayName($host);
            $misc = $this->decodeMisc($host['misc']);
            if (isset($misc['status'])) {
                continue;
            }
            $host['misc'] = $misc;
            // TODO: misc array must be in misc key this merge is temporary for compatibility
            $host = array_merge($host, $misc);

            if (
                // All
                $status === null ||
                // Off
                ($status === 0 && (int) $host['online'] === 0) ||
                // On
                ($status === 1 && (int) $host['online'] === 1) ||
                // Fail
                ($status === 2 && $host['ansible_fail'])
            ) {
                $result_hosts[] = $host;
            }
        endforeach;

        return $result_hosts;
    }
    /**
     * Status (null All) (0 Off) (1 On) (2 Missing Pings)
     * @param int|null $status
     * @return list<array<string, mixed>>
     */
    public function getAgentsHosts(?int $status = null): array
    {
        $hosts = $this->cmdHostModel->getAgentsHosts();
        $result_hosts = [];

        foreach ($hosts as $host) :
            $host['display_name'] = $this->hostFormatter->getDisplayName($host);

            $misc = $this->decodeMisc($host['misc']);
            $host['misc'] = $misc;
            // TODO: misc array must be in misc key this merge is temporary for compatibility
            $host = array_merge($host, $misc);

            // All
            if ($status === null) :
                $result_hosts[] = $host;
            endif;
            // Off
            if ($status === 0 && (int) $host['online'] === 0) :
                $result_hosts[] = $host;
            endif;
            // On
            if ($status === 1 && (int) $host['online'] === 1) :
                $result_hosts[] = $host;
            endif;
            // Ping Fail
            if ($status === 2 && !empty($host['misc']['agent_missing_pings'])) :
                $result_hosts[] = $host;
            endif;
        endforeach;

        return $result_hosts;
    }

    /**
     * Recogemos las alertas, quitamos duplicados y mostramos las 4 ultimas
     * @return array<int,array<string,string>>
     */
    public function getAlertHosts(): array
    {

        $hosts = $this->cmdHostModel->getAlertOn();
        $log_type = [ \LogType::EVENT_ALERT ];
        $result_hosts = [];

        foreach ($hosts as $host) :
            $host['display_name'] = $this->hostFormatter->getDisplayName($host);
            $min_host = [
                'id' => $host['id'],
                'display_name' => $host['display_name'],
                'mac' => $host['mac'],
                'ip' => $host['ip'],
                'online' => $host['online'],
            ];

            if ($host['alert'] && empty($host['misc']['disable_alarms'])) :
                $logs_opt = [
                    'log_type' => $log_type,
                    'host_id' => $host['id'],
                ];
                $alert_logs = $this->logHostsModel->getLogsHosts($logs_opt);

                if (!empty($alert_logs)) :
                    $min_host['log_msgs'] = $this->hostFormatter->fHostLogsMsgs($alert_logs);
                endif;
            endif;
            $result_hosts[] = $min_host;
        endforeach;

        return $result_hosts;
    }

    /**
     * Recogemos los warnings, quitamos duplicados y mostramos las 4 ultimas
     * 2 port_wartn, 4 warn
     * @return array<int,array<string,string>>
     */
    public function getWarnHosts(): array
    {
        $hosts = $this->cmdHostModel->getWarnOn();
        $result_hosts = [];
        $log_type = [ \LogType::EVENT_WARN ];

        foreach ($hosts as $host) :
            $host['display_name'] = $this->hostFormatter->getDisplayName($host);
            $min_host = [
                'id' => $host['id'],
                'display_name' => $host['display_name'],
                'mac' => $host['mac'],
                'ip' => $host['ip'],
                'online' => $host['online'],
            ];

            if ($host['warn'] && empty($host['misc']['disable_alarms'])) :
                $logs_opt = [
                    'log_type' => $log_type,
                    'host_id' => $host['id'],
                ];
                $warn_logs = $this->logHostsModel->getLogsHosts($logs_opt);

                if (!empty($warn_logs)) :
                    $min_host['log_msgs'] = $this->hostFormatter->fHostLogsMsgs($warn_logs);
                endif;
                $result_hosts[] = $min_host;
            endif;
        endforeach;

        return $result_hosts;
    }

    /**
     *
     * @param array<string, mixed> $misc
     * @return string|false
     */
    public function encodeMisc(array $misc): string|false
    {
        if (empty($misc)) {
            return false;
        }
        $misc = json_encode($misc, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::warning('Error encodeMisc: Invalid JSON');

            return false;
        }

        return $misc;
    }

    /**
     *
     * @param string $misc
     * @return array<string, string|int>
     */
    public function decodeMisc(string $misc): array
    {
        if (empty($misc)) {
            return [];
        }
        $misc = json_decode($misc, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::warning('Error decodeMisc: Invalid JSON');

            return ['status' => 'error'];
        }

        return $misc;
    }

    /**
     * Procesa la actualización de un host, manejando el campo misc correctamente.
     *
     * @param int $id del host a actualizar.
     * @param array $data datos a actualizar (incluyendo misc).
     *
     * @return array<string|int>
     */
    public function updateHost(int $id, array $data): array
    {
        if (isset($data['misc'])) {
            if (!is_array($data['misc'])) {
                return ['error', 'error_msg' => 'Misc value is set but not an array'];
            }
            //Database Current Misc
            $host_misc  = $this->cmdHostModel->getMiscById($id);
            $currentMisc = $this->decodeMisc($host_misc['misc']);
            if (isset($currentMisc['error'])) {
                return $currentMisc;
            }

            $newMisc = array_merge($currentMisc, $data['misc']);

            $newMiscEncoded = $this->encodeMisc($newMisc);

            if ($newMiscEncoded === false) {
                return ['error', 'error_msg' => 'Error encoding misc'];
            }

            $data['misc'] = $newMiscEncoded;
        }

        if ($this->cmdHostModel->updateByID($id, $data)) {
            return ['success' => true];
        }

        return ['error', 'error_msg' => 'Error updating host'];
    }

    /**
     *
     * @param int $id
     * @param string $msg
     * @param int $log_type
     * @return void
     */
    public function setAlertOn(int $id, string $msg, int $log_type, int $event_type): void
    {
        \Log::logHost(\LogLevel::ALERT, $id, $msg, $log_type, $event_type);
        $this->cmdHostModel->updateByID($id, ['alert' => 1]);
    }

    /**
     *
     * @param int $id
     * @param string $msg
     * @param int $log_type
     * @return void
     */
    public function setWarnOn(int $id, string $msg, int $log_type, int $event_type): void
    {
        \Log::logHost(\LogLevel::WARNING, $id, $msg, $log_type, $event_type);
        $this->cmdHostModel->updateByID($id, ['warn' => 1]);
    }

    /**
     *
     * @param int $id
     * @param array<string, string|int> $port_update
     * @return bool
     */
    public function updatePort(int $id, array $port_update): bool
    {
        return $this->cmdHostModel->updatePort($id, $port_update);
    }

    public function switchHostsNetwork(int $n_origin_id, int $n_new_id): bool
    {
        $field['network'] = $n_new_id;
        $condition = 'network = :origin_network';
        $params['origin_network'] = $n_origin_id;

        return $this->cmdHostModel->update($field, $condition, $params);
    }
}
