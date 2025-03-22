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
use App\Models\CmdHostLogsModel;

class HostService
{
    private CmdHostModel $cmdHostModel;
    private CmdHostLogsModel $cmdHostLogsModel;
    private HostFormatter $hostFormatter;
    private AnsibleService $ansibleService;

    private \AppContext $ctx;
    private \Config $ncfg;

    public function __construct(\AppContext $ctx)
    {
        $this->cmdHostModel = new CmdHostModel($ctx);
        $this->cmdHostLogsModel = new CmdHostLogsModel($ctx);
        $this->hostFormatter = new HostFormatter($ctx);
        $this->ansibleService = new AnsibleService($ctx);
        $this->cmdHostLogsModel = new CmdHostLogsModel($ctx);
        $this->ncfg = $ctx->get('Config');
        $this->ctx = $ctx;
    }

    /**
     *
     * @param int $target_id
     * @return array<string, string|int>
     */
    public function getDetails(int $target_id): array
    {
        $hostDetails = $this->cmdHostModel->getHostDetails($target_id);

        if (!$hostDetails) {
            return ['error' => 'No details found for the host'];
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

    /**
     *
     * @param int $target_id
     * @return array<string, string|int>
     */
    public function getDetailsStats(int $target_id): array
    {
        $hostDetails = $this->cmdHostModel->getHostDetailsStats($target_id);

        if (!$hostDetails) {
            return ['error' => 'No details found for the host'];
        }

        $this->hostFormatter->formatMisc($hostDetails);

        if (!empty($hostDetails['load_avg'])) {
            $hostDetails_stats['load_avg'] = $hostDetails['load_avg'];
        }
        if (!empty($hostDetails['mem_ifno'])) {
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
     * @return list<array<string, mixed>>
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
            $misc = $this->hostFormatter->decodeMisc($host['misc']);
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

            $misc = $this->hostFormatter->decodeMisc($host['misc']);
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
            if ($status === 2 && !empty($host['agent_missing_pings'])) :
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

            if ($host['alert'] && empty($host['disable_alarms'])) :
                $logs_opt = [
                    'log_type' => $log_type,
                    'host_id' => $host['id'],
                ];
                $alert_logs = $this->cmdHostLogsModel->getLogsHosts($logs_opt);

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

            if ($host['warn'] && empty($host['disable_alarms'])) :
                $logs_opt = [
                    'log_type' => $log_type,
                    'host_id' => $host['id'],
                ];
                $warn_logs = $this->cmdHostLogsModel->getLogsHosts($logs_opt);

                if (!empty($warn_logs)) :
                    $min_host['log_msgs'] = $this->hostFormatter->fHostLogsMsgs($warn_logs);
                endif;
                $result_hosts[] = $min_host;
            endif;
        endforeach;

        return $result_hosts;
    }
}
