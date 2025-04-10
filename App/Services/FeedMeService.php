<?php

/**
 * FeedMeService
 *
 * Servicio para procesar solicitudes de agentes y manejar actualizaciones de hosts.
 *
 * @package App\Services
 * @subpackage FeedMeService
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Services\HostService;
use App\Services\DateTimeService;
use App\Models\CmdHostModel;
use App\Models\CmdStats;

class FeedMeService
{
    /**
     * @var \AppContext $ctx Contexto
     */
    private \AppContext $ctx;

    /**
     * @var HostService $hostService Servicio para manejar hosts.
     */
    private HostService $hostService;

    /**
     * @var CmdStats $cmdStats Model Stats
     */
    private CmdStats $cmdStats;

    /**
     * @var CmdHostModel $cmdHostModel Model host data
     */
    private CmdHostModel $cmdHostModel;

    /**
     * @var \Config $ncfg Config
     */
    private \Config $ncfg;

    /**
     * Constructor de FeedMeService.
     *
     * @param \AppContext $ctx Contexto de la aplicación.
     */
    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get('Config');
        $this->hostService = new HostService($ctx);
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        unset($this->ctx, $this->hostService);
    }

    /**
     * Procesa una solicitud del agente.
     *
     * @param array<string, mixed> $request Datos de la solicitud.
     * @return array<string, mixed> Respuesta procesada.
     */
    public function processRequest(array $request): array
    {
        $command = $request['cmd'];
        $host_id = (int) $request['id'];
        $host = $this->hostService->getHostById($host_id);
        $rdata = $request['data'];
        $host_update_values = [];

        $validated_response = $this->validateHostRequest($host, $request['token'], $host_id);
        if (!empty($validated_response['error'])) {
            return $validated_response;
        }

        //$agent_logId = '[AGENT v' . $request['version'] . '][' . $host['display_name'] . '] ';

        $agent_default_interval = $this->getAgentInterval();

        $host_update_values = $this->prepareHostUpdateValues($host, $request, $agent_default_interval);
        if (empty($host['agent_installed'])) {
            $host_update_values['agent_installed'] = 1;
        }

        if (!empty($request['name'])) {
            switch ($request['name']):
                case 'ping': //Ping come with real time data
                    $ping_updates = $this->processPingData($host_id, $host_update_values, $rdata);
                    if(!empty($ping_updates)) {
                        $host_update_values = array_merge($host_update_values, $ping_updates);
                    }
                    break;
                case 'send_stats': // Stats every 5min
                    $this->processStats($host_id, $rdata);
                    break;
                case 'listen_ports_info': //List ports and changes
                    $this->processPorts($host_id, $rdata);
                    break;
                case 'starting': //Only Startup info
                    $starting_updates = $this->processStarting($host_id, $rdata);
                    if (!empty($starting_updates)) {
                        $host_update_values = array_merge($host_update_values, $starting_updates);
                    }
                    break;
                case 'high_iowat':
                case 'high_cpu_usage':
                case 'high_memory_usage':
                case 'high_disk_usage':
                case 'agent_shutdown':
                    $this->notificationLog($request['name'], $host_id,  $rdata);
                    break;
                default:
                    \Log::warning('Notification receive with unknown reference: ' . $rdata['name']);
            endswitch;
        }

        $update_response = $this->hostService->updateHost($host['id'], $host_update_values);
        if(!empty($update_response['error'])) {
            return $update_response;
        }

        $response = $this->prepareResponse($command, $request, $agent_default_interval);

        return ['success' => true, 'response_data' => $response];
    }

    /**
     * Procesa datos de inicio enviados por el agente.
     *
     * @param int $host_id ID del host.
     * @param array<string, string|int> $rdata Datos enviados por el agente.
     * @return array<string, string|int> Valores actualizados del host.
     */
    public function processStarting(int $host_id, array $rdata): array
    {
        $host = $this->hostService->getHostById($host_id);
        $host_update_values = [];

        if (!empty($rdata['ncpu'])) {
            if (!isset($host['misc']['ncpu']) || ($rdata['ncpu'] !== $host['ncpu'])) {
                $host_update_values['misc']['ncpu'] = $rdata['ncpu'];
            }
        }
        if (!empty($rdata['uptime'])) {
            if (!isset($host['misc']['uptime']) || ($rdata['uptime'] !== $host['uptime'])) {
                $host_update_values['misc']['uptime'] = $rdata['uptime'];
            }
        }

        return $host_update_values;
    }

    /**
     * Procesa estadísticas enviadas por el agente.
     *
     * @param int $host_id ID del host.
     * @param array<string, string|int> $rdata Datos de estadísticas.
     * @return bool Indica si el procesamiento fue exitoso.
     */
    public function processStats(int $host_id, array $rdata): bool
    {
        if (empty($this->cmdStats)) {
            $this->cmdStats = new CmdStats($this->ctx);
        }

        $dateTimeService  = new DateTimeService();

        if (!\isEmpty($rdata['load_avg_stats'])) {
            $stats_data = [
                'date' => $dateTimeService->dateNow(),
                'type' => 2,   //loadavg
                'host_id' => $host_id,
                'value' => $rdata['load_avg_stats']['5min']
            ];
            $this->cmdStats->insertStats($stats_data);
        }

        if (!\isEmpty($rdata['iowait_stats'])) {
            $stats_data = [
                'date' => $dateTimeService->dateNow(),
                'type' => 3,   //iowait
                'host_id' => $host_id,
                'value' => $rdata['iowait_stats']
            ];
            $this->cmdStats->insertStats($stats_data);
        }

        if (!\isEmpty($rdata['mem_stats'])) {
            $stats_data = [
                'date' => $dateTimeService->dateNow(),
                'type' => 4,   // Memory
                'host_id' => $host_id,
                'value' => $rdata['mem_stats']
            ];
            $this->cmdStats->insertStats($stats_data);
        }

        return true;
    }

    /**
     * Procesa port info provided by agent
     *
     * @param int $host_id ID del host.
     * @param array<string, string|int> $rdata Datos de puertos.
     * @return bool Indica si el procesamiento fue exitoso.
     */
    public function processPorts(int $host_id, array $rdata): bool
    {
        if (empty($rdata['listen_ports_info']) || !is_array($rdata['listen_ports_info'])) {
            return false;
        }

        $this->updateListenPorts($host_id, $rdata['listen_ports_info']);

        return true;
    }

    /**
     * Update ports
     *
     * @param int $host_id ID del host.
     * @param array<string, string|int> $listen_ports Ports
     * @return bool success|fail.
     */
    public function updateListenPorts(int $host_id, array $listen_ports): bool
    {
        $scan_type = 2; // Agent Based

        if(!isset($this->cmdHostModel)) {
            $this->cmdHostModel = new CmdHostModel($this->ctx);
        }
        $dateTimeService  = new DateTimeService();

        $db_host_ports = $this->cmdHostModel->getHostScanPorts($host_id, $scan_type);
        $db_ports_map = [];

        // Normalize TODO: to method?
        foreach ($db_host_ports as $db_port) {
            $interface = $db_port['interface'] ?? '';
            $pnumber = (int) $db_port['pnumber'];
            $protocol = (int) $db_port['protocol'];

            if ($db_port['ip_version'] === 'ipv6' && strpos($interface, ':') !== false && $interface[0] !== '[') {
                $interface = "[{$interface}]";
            }

            $key = "{$protocol}:$pnumber:{$interface}:{$db_port['ip_version']}";
            $db_ports_map[$key] = $db_port;
        }

        foreach ($listen_ports as $port) {
            $interface = $port['interface'] ?? '';
            $pnumber = (int)$port['port'];
            $protocol = ($port['protocol'] === 'tcp') ? 1 : 2;

            if ($port['ip_version'] === 'ipv6' && strpos($interface, ':') !== false && $interface[0] !== '[') {
                $interface = "[{$interface}]";
            }
            $ip_version = $port['ip_version'] ?? '';

            $key = "{$protocol}:{$pnumber}:{$interface}:{$port['ip_version']}";

            if (isset($db_ports_map[$key])) {
                $db_port = $db_ports_map[$key];

                if ($db_port['service'] !== $port['service']) {
                    $warnmsg = 'Service name change detected: '
                        . "({$db_port['service']}->{$port['service']}) ({$pnumber})";
                    $this->hostService->setWarnOn($host_id, $warnmsg, \LogType::EVENT_WARN, \EventType::SERVICE_NAME_CHANGE);

                    $this->hostService->updatePort($db_port['id'], [
                        "service" => $port['service'],
                        "online" => 1,
                        "last_change" => $dateTimeService->dateNow(),
                    ]);
                } elseif ($db_port['online'] == 0) {
                    $alertmsg = "Port UP detected: ({$port['service']}) ($pnumber)";
                    $this->hostService->setWarnOn($host_id, $alertmsg, \LogType::EVENT_WARN, \EventType::PORT_UP);

                    $this->hostService->updatePort($db_port['id'], [
                        "online" => 1,
                        "last_change" => $dateTimeService->dateNow(),
                    ]);
                }

                unset($db_ports_map[$key]);
            } else {
                \Log::warning($key);
                $new_port_data = [
                    'hid' => $host_id,
                    'scan_type' => $scan_type,
                    'protocol' => $protocol,
                    'pnumber' => $pnumber,
                    'online' => 1,
                    'service' => $port['service'],
                    'interface' => $interface,
                    'ip_version' => $ip_version,
                    'last_change' => $dateTimeService->dateNow(),
                ];
                $this->cmdHostModel->addPort($new_port_data);
                $log_msg = "New port detected: $pnumber ({$port['service']})";
                $this->hostService->setAlertOn($host_id, $log_msg, \LogType::EVENT_ALERT, \EventType::PORT_NEW);
                unset($db_ports_map[$key]);
            }
        }

        foreach ($db_ports_map as $db_port) {
            if ($db_port['online'] == 1) {
                $set = [
                    'online' => 0,
                    'last_change' => $dateTimeService->dateNow(),
                ];
                $alertmsg = "Port DOWN detected: {$db_port['pnumber']} ({$db_port['service']})";
                $this->hostService->setAlertOn($host_id, $alertmsg, \LogType::EVENT_ALERT, \EventType::PORT_DOWN);
                $this->hostService->updatePort($db_port['id'], $set);
            }
        }

        return true;
    }

    /**
     * Ping data. Agent Provided
     *
     * @param int $host_id ID del host.
     * @param array<string, string|int> $host_update_values  Update fields
     * @param array<string, mixed> $rdata
     * @return array<string, string|int> return added values.
     */
    private function processPingData(int $host_id, array $host_update_values, array $rdata): array
    {
        if (!isEmpty($rdata['loadavg'])) {
            $host_update_values['misc']['load_avg'] = serialize($rdata['loadavg']);
        }
        if (!isEmpty($rdata['meminfo'])) {
            $host_update_values['misc']['mem_info'] = serialize($rdata['meminfo']);
        }
        if (!isEmpty($rdata['misc']['disksinfo'])) {
            $host_update_values['disks_info'] = serialize($rdata['disksinfo']);
        }
        if (!isEmpty($rdata['iowait'])) {
            $host_update_values['misc']['iowait'] = $rdata['iowait'];
        }
        if (!isEmpty($rdata['host_logs'])) :
            foreach($rdata['host_logs'] as $hlog) {
                \Log::logHost($hlog['level'], $host_id, 'Agent: ' . $hlog['message']);
            }
        endif;

        return $host_update_values;
    }

    /**
     * Response to agent
     *
     * @param string $command Command
     * @param array<string, string|int> $request
     * @param int $interval
     * @return array<string, string|int> response
     */
    private function prepareResponse(string $command, array $request, int $interval): array
    {
        $response = [
            'cmd' => $command,
            'token' => $request['token'],
            'version' => $this->ncfg->get('agent_min_version'),
            'response_msg' => null,
            'refresh' => $interval,
            'data' => []
        ];

        switch ($command) {
            case 'ping':
                $response['cmd'] = 'pong';
                $response['response_msg'] = true;
                return $response;
            case 'notification':
                return [];
        }

        return [];
    }

    /**
     * Validate request
     *
     * @param array<string, string|int> $host host data
     * @param string $token Auth Toeken
     * @param int $host_id  Host id.
     * @return array<string, string|int> result array success|error.
     */
    private function validateHostRequest(array $host, string $token, int $host_id): array
    {
        if (!$host) {
            \Log::error("Host not found, requested id:" . $host_id);
            return ['error' => 'Host not found'];
        } elseif (empty($host['token']) || $host['token'] !== $token) {
            \Log::warning("Invalid Token receive from id:" . $host_id);
            return ['error' => 'Invalid Token'];
        }

        return ['success'];
    }

    /**
     * Obtiene el intervalo predeterminado para el agente.
     *
     * @return int Intervalo en segundos.
     */
    private function getAgentInterval(): int
    {
        $agent_default_interval = $this->ncfg->get('agent_default_interval');
        $last_refreshing = (int) $this->ncfg->get('refreshing');
        $refresh_time_seconds = (int) $this->ncfg->get('refresher_time') * 60;

        if ((time() - $last_refreshing) < $refresh_time_seconds) {
            $agent_default_interval = 5;
        }

        return $agent_default_interval;
    }

    /**
     * Prepara los valores de actualización del host.
     *
     * @param array<string, string|int> $host Datos actuales del host.
     * @param array<string, string|int> $request Datos de la solicitud.
     * @param int $interval Intervalo de actualización.
     * @return array<string, string|int> Valores preparados para la actualización.
     */
    private function prepareHostUpdateValues(array $host, array $request, int $interval): array
    {
        $values['misc'] = [
            'agent_next_report' => time() + (int) $interval,
            'agent_last_contact' => time(),
            'agent_online' => 1
        ];

        if (empty($host['misc']['agent_version']) || $host['misc']['agent_version'] != (string) $request['version']) {
            $values['misc']['agent_version'] = (string) $request['version'];
        }

        if ((int) $host['online'] !== 1) {
            $values['online'] = 1;
        }

        return $values;
    }

    /**
     * Registra un log de notificación.
     *
     * @param string $request_name Nombre de la solicitud.
     * @param int $host_id ID del host.
     * @param array<string, mixed> $rdata Datos de la notificación.
     * @return void
     */
    private function notificationLog(string $request_name, int $host_id, array $rdata): void
    {
        $event_type = !empty($rdata['event_type']) ? $rdata['event_type'] : 0;
        $log_type = isset($rdata['log_type']) ? $rdata['log_type'] : (!empty($rdata['event_type']) ? \LogType::EVENT : 0);
        $log_level = isset($rdata['log_level']) ? $rdata['log_level'] : 7;
        $log_msg = "Notification: $request_name";
        isset($rdata['msg']) ? $log_msg .= ': ' . $rdata['msg'] : null;
        if (!empty($rdata['event_value'])) {
            $log_msg .= ' Event value: ' . $rdata['event_value'];
        }

        if ($log_level <= \LogLevel::CRITICAL) {
            $this->hostService->setAlertOn($host_id, $log_msg, \LogType::EVENT_ALERT, $event_type);
        } elseif ($log_level == \LogLevel::ERROR || $log_level == \LogLevel::WARNING) {
            $this->hostService->setWarnOn($host_id, $log_msg, \LogType::EVENT_WARN, $event_type);
        } else {
            \Log::logHost($log_level, $host_id, $log_msg, $log_type, $event_type);
        }
    }
}
