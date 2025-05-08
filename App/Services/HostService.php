<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Models\CmdHostModel;
use App\Models\LogHostsModel;
use App\Models\HostsModel;
use App\Services\DateTimeService;
use App\Services\Filter;
use App\Services\NetworksService;

class HostService
{
    /**
     * @var CmdHostModel
     */
    private CmdHostModel $cmdHostModel;
    /**
     * @var LogHostsModel
     */
    private LogHostsModel $logHostsModel;
    /**
     * @var HostFormatter
     */
    private HostFormatter $hostFormatter;
    /**
     * @var AnsibleService
     */
    private AnsibleService $ansibleService;
    /**
     * @var HostsModel
     */
    private HostsModel $hostsModel;

    /**
     * @var \AppContext
     */
    private \AppContext $ctx;
    /**
     * @var \Config
     */
    private \Config $ncfg;
    /**
     * @var \DBManager
     */
    private \DBManager $db;

    /**
     * @var array
     */
    private array $lng;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get('Config');
        $this->lng = $ctx->get('lng');
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

    /**
     *
     * @param array<string, int|string> $host_data
     * @return array<string, string|int>
     */
    public function add(array $host): array
    {
        // Added by hostname we need the ip always
        if (empty($host['ip']) && !empty($host['hostname'])) {
            if (!Filter::varDomain($host['hostname'])) {
                return ['status' => 'error', 'error_msg' => 'Adding host: Wrong hostname'];
            }
            $host['ip'] = gethostbyname($host['hostname']);
        }
        if (!$host['ip']) {
            return ['status' => 'error', 'error_msg' => 'Adding host: No IP or can not resolve hostname'];
        }

        if (empty($host['hostname'])) {
            $host['hostname'] = gethostbyaddr($host['ip']);
        }

        if(!empty($host['misc'])) {
            $encoded_misc = $this->encodeMisc($host['misc']);
            if (!$encoded_misc) {
                return ['status' => 'error', 'error_msg' => 'Adding host: Error encoding misc'];
            }
            $host['misc'] = $encoded_misc;
            unset($encoded_misc);
        }

        $networkService = new NetworksService($this->ctx);
        $network_match = $networkService->matchNetwork($host['ip']);

        $this->hostsModel->add($host);

        $host_id = $this->hostsModel->insertId();
        if (!empty($host['hostname'])) {
            $display_name = $host['hostname'];
        } else {
            $display_name = $host['ip'];
        }

        if (
            !valid_array($network_match) ||
            $networkService->isLocal($host['ip']) && $network_match['network'] == '0.0.0.0/0'
        ) {
            return ['status' => 'error', 'error_msg' => $this->lng['L_ERR_NOT_NET_CONTAINER']];
        } else {
            if ($this->getHostByIP($host['ip'])) {
                return ['status' => 'error', 'error_msg' => $this->lng['L_ERR_DUP_IP']];
            }
        }

        $host['network'] = $network_match['id'];

        $this->hostsModel->add($host);

        $log_msg = 'Found new host: ' . $display_name . ' on network ' . $network_match['name'];
        \Log::logHost(
            \LogLevel::WARNING,
            $host_id,
            $log_msg,
            \LogType::EVENT_WARN,
            \EventType::NEW_HOST_DISCOVERY
        );

        return ['status' => 'success', 'response_msg' => $this->lng['L_OK']];
    }

    /**
     * @param int $id
     * @return array<string, string|int>
     */
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

        if (!empty($hostDetails['misc'])) {
            $hostDetails['misc'] = $this->decodeMisc($hostDetails['misc']);
            /* TODO: Migrate: keep misc values in misc then delete this */
            $hostDetails = array_merge($hostDetails, $hostDetails['misc']);
        }

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

        $hostDetails = $this->hostFormatter->format($hostDetails);

        // Get remote  ports (1)
        $hostDetails['remote_ports'] = $this->cmdHostModel->getHostScanPorts($target_id, 1);
        // Agent provided port list (2)
        if ($hostDetails['agent_installed']) {
            $agent_ports = $this->cmdHostModel->getHostScanPorts($target_id, 2);

            // Agent ports are sorted by online status
            usort($agent_ports, function($a, $b) {
                if ($a['online'] == $b['online']) {
                    return 0;
                }
                return ($a['online'] < $b['online']) ? -1 : 1;
            });
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
     * @param array<string, mixed> $filters
     * @return array
     */
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
        $dateTime = new DateTimeService();
        $update = [
            'alert' => 1,
            'glow' => $dateTime->dateNow(),
        ];
        $this->cmdHostModel->updateByID($id, $update);
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
        $dateTime = new DateTimeService();
        $update = [
            'warn' => 1,
            'glow' => $dateTime->dateNow(),
        ];
        $this->cmdHostModel->updateByID($id, $update);
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

    /**
     *
     * @param int $n_origin_id
     * @param int $n_new_id
     * @return bool
     */
    public function switchHostsNetwork(int $n_origin_id, int $n_new_id): bool
    {
        $field['network'] = $n_new_id;
        $condition = 'network = :origin_network';
        $params['origin_network'] = $n_origin_id;

        return $this->cmdHostModel->update($field, $condition, $params);
    }

    /**
     *
     * @param int $hid
     * @return string|null
     */
    public function createToken(int $hid): ?string
    {
        $token = bin2hex(random_bytes(16));
        if ($this->cmdHostModel->submitHostToken($hid, $token)) {
            return $token;
        }

        return null;
    }

    public function getHostByIP(string $ip): array
    {
        return $this->hostsModel->getHostByIP($ip);
    }

    public function getAll(): array
    {
        return $this->hostsModel->getAll();
    }

    /**
     *
     * @param int $network_id
     * @return array<string,mixed>
     */
    public function getHostsByNetworkId(int $network_id): array
    {
        return $this->hostsModel->getHostsByNetworkId($network_id);
    }
}
