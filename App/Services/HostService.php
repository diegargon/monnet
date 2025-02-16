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

    private \Config $ncfg;

    public function __construct(\AppContext $ctx)
    {
        $this->cmdHostModel = new CmdHostModel($ctx);
        $this->cmdHostLogsModel = new CmdHostLogsModel($ctx);
        $this->hostFormatter = new HostFormatter($ctx);
        $this->ansibleService = new AnsibleService($ctx);
        $this->cmdHostLogsModel = new CmdHostLogsModel($ctx);
        $this->ncfg = $ctx->get('Config');
    }

    public function getDetails(int $target_id): array
    {
        $hostDetails = $this->cmdHostModel->getHostDetails($target_id);

        if (!$hostDetails) {
            return ['error' => 'No details found for the host'];
        }

        $hostDetails = $this->hostFormatter->format($hostDetails);

        #$hostDetails['logs'] = $this->logService->getHostLogs($target_id);
        // Obtener puertos remotos
        //$hostDetails['remote_ports'] = $this->cmdHostModel->getRemotePorts($target_id);

        // Obtener logs del host
        //$hostDetails['logs'] = $this->logService->getHostLogs($target_id);

        // Obtener métricas del host
        //$hostDetails['metrics'] = $this->getHostMetrics($target_id);



        // Obtener detalles de Ansible (si está habilitado)
        if ($hostDetails['ansible_enabled']) {
            $hostDetails['ansible_reports'] = $this->ansibleService->getReports($target_id);
        }

        return $hostDetails;
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
            $misc = $this->hostFormatter->fMisc($host['misc']);
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
            // Fail
            if ($status === 2 && $host['ansible_fail']) :
                $result_hosts[] = $host;
            endif;
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

            $misc = $this->hostFormatter->fMisc($host['misc']);
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
        $result_hosts = [];

        foreach ($hosts as $host) :
            $alert_logs_msgs = [];
            $host['display_name'] = $this->hostFormatter->getDisplayName($host);
            $alert_logs_items = [];
            $min_host = [
                'id' => $host['id'],
                'display_name' => $host['display_name'],
                'mac' => $host['mac'],
                'ip' => $host['ip'],
                'online' => $host['online'],
            ];

            if ($host['alert'] && empty($host['disable_alarms'])) :
                $log_type = [ \LogType::EVENT_ALERT ];

                $logs_opt = [
                    'log_type' => $log_type,
                    'host_id' => $host['id'],
                ];
                $alert_logs = $this->cmdHostLogsModel->getLogsHosts($logs_opt);

                if (!empty($alert_logs)) :
                    $min_host['log_msgs'] = $this->hostFormatter->fHostLogsMsgs($alert_logs);
                    $result_hosts[] = $min_host;
                else :
                    $min_host['alert_msg']  = 'Alert logs are empty';
                endif;
            endif;
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
                $log_type = [ \LogType::EVENT_WARN ];

                $logs_opt = [
                    'log_type' => $log_type,
                    'host_id' => $host['id'],
                ];
                $warn_logs = $this->cmdHostLogsModel->getLogsHosts($logs_opt);

                if (!empty($warn_logs)) :
                    $min_host['log_msgs'] = $this->hostFormatter->fHostLogsMsgs($warn_logs);
                    $result_hosts[] = $min_host;
                else :
                    $min_host['warn_msg']  = 'Warn logs are empty';
                endif;
            endif;
        endforeach;

        return $result_hosts;
    }
}
