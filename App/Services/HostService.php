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

class HostService
{
    private CmdHostModel $cmdHostModel;
    private HostFormatter $hostFormatter;
    private LogService $logService;
    private AnsibleService $ansibleService;

    public function __construct(\AppContext $ctx)
    {
        $this->cmdHostModel = new CmdHostModel($ctx);
        $this->hostFormatter = new HostFormatter($ctx);
        $this->logService = new LogService($ctx);
        $this->ansibleService = new AnsibleService($ctx);
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
        $result_hosts = [];

        if (!$this->ncfg->get('ansible')) :
            return [];
        endif;

        foreach ($this->hosts as $host) :
            if (empty($host['ansible_enabled'])) :
                continue;
            endif;
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
        $result_hosts = [];

        foreach ($this->hosts as $host) :
            if (empty($host['agent_installed'])) :
                continue;
            endif;
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
            $alert_logs_items = [];
            $min_host = [
                'id' => $host['id'],
                'display_name' => $host['display_name'],
                'mac' => $host['mac'],
                'ip' => $host['ip'],
                'online' => $host['online'],
            ];

            if ($host['alert'] && empty($host['disable_alarms'])) :
                $log_type = [ LogType::EVENT_ALERT ];

                $opt = [
                    'log_type' => $log_type,
                    'host_id' => $host['id'],
                ];
                $alert_logs = Log::getLogsHosts($opt);

                if (!empty($alert_logs)) :
                    foreach ($alert_logs as $item) :
                        if (!in_array($item['msg'], $alert_logs_msgs)) :
                            $alert_logs_msgs[] = $item['msg'];
                            $alert_logs_items[] = $item;
                        endif;
                    endforeach;

                    $timezone = $this->ncfg->get('timezone');
                    $timeformat = $this->ncfg->get('datetime_format_min');
                    foreach ($alert_logs_items as $item) :
                        $date = utc_to_tz($item['date'], $timezone, $timeformat);
                        $min_host['log_msgs'][] = [
                            'log_id' => $item['id'],
                            'log_type' => LogType::getName($item['log_type']),
                            'event_type' => EventType::getName($item['event_type']),
                            'msg' => "{$item['msg']} - $date",
                            'ack_state' => $item['ack']
                        ];
                    endforeach;
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

        foreach ($this->hosts as $host) :
            $min_host = [
                'id' => $host['id'],
                'display_name' => $host['display_name'],
                'mac' => $host['mac'],
                'ip' => $host['ip'],
                'online' => $host['online'],
            ];

            if ($host['warn'] && empty($host['disable_alarms'])) :
                $log_type = [ LogType::EVENT_WARN ];

                $opt = [
                    'log_type' => $log_type,
                    'host_id' => $host['id'],
                ];
                $warn_logs = Log::getLogsHosts($opt);
                $warn_logs_msgs = [];
                $warn_logs_items = [];

                if (!empty($warn_logs)) :
                    foreach ($warn_logs as $item) :
                        if (!in_array($item['msg'], $warn_logs_msgs)) :
                            $warn_logs_msgs[] = $item['msg'];
                            $warn_logs_items[] = $item;
                        endif;
                    endforeach;
                    $timezone = $this->ncfg->get('timezone');
                    $timeformat = $this->ncfg->get('datetime_format_min');
                    foreach ($warn_logs_items as $item) :
                        $date = utc_to_tz($item['date'], $timezone, $timeformat);
                        $min_host['log_msgs'][] = [
                            'log_id' => $item['id'],
                            'log_type' => LogType::getName($item['log_type']),
                            'event_type' => EventType::getName($item['event_type']),
                            'msg' => "{$item['msg']} - $date",
                            'ack_state' => $item['ack']
                        ];
                    endforeach;
                    $result_hosts[] = $min_host;
                else :
                    $min_host['warn_msg']  = 'Warn logs are empty';
                endif;
            endif;
        endforeach;

        return $result_hosts;
    }
}
