<?php

/**
 * FeedMeService
 *
 * Service to process agent requests and handle host updates.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Services\HostService;
use App\Services\DateTimeService;
use App\Models\CmdHostModel;
use App\Models\CmdStatsModel;
use App\Services\Filter;

class FeedMeService
{
    /**
     * @var \AppContext $ctx Context
     */
    private \AppContext $ctx;

    /**
     * @var HostService $hostService Service to handle hosts.
     */
    private HostService $hostService;

    /**
     * @var CmdStatsModel $cmdStatsModel Model Stats
     */
    private CmdStatsModel $cmdStatsModel;

    /**
     * @var CmdHostModel $cmdHostModel Model host data
     */
    private CmdHostModel $cmdHostModel;

    /**
     * @var \Config $ncfg Config
     */
    private \Config $ncfg;

    /**
     * @var \DBManager
     */
    private \DBManager $db;

    /** @var DateTimeService */
    private DateTimeService $datetimeService;
    /**
     * Constructor of FeedMeService.
     *
     * @param \AppContext $ctx Application context.
     */
    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get('Config');
        $this->db = $ctx->get('DBManager');
        $this->hostService = new HostService($ctx);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        unset($this->ctx, $this->hostService);
    }

    /**
     * Processes an agent request.
     *
     * @param array<string, mixed> $request Request data.
     * @return array<string, mixed> Processed response.
     */
    public function processRequest(array $request): array
    {
        try {
            $command = $request['cmd'];
            $host_id = (int) $request['id'];
            $host = $this->hostService->getHostById($host_id);
            if (!$host) {
                return ['error' => 'Host not found'];
            }
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
                switch ($request['name']) {
                    case 'ping': //Ping come with real time data
                        $ping_updates = $this->processPingData($host_id, $host_update_values, $rdata);
                        if (!empty($ping_updates)) {
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
                        $starting_updates = $this->processStarting($host, $rdata);
                        if (!empty($starting_updates)) {
                            $host_update_values = array_merge($host_update_values, $starting_updates);
                        }
                        break;
                    case 'high_iowait':
                    case 'high_cpu_usage':
                    case 'high_memory_usage':
                    case 'high_disk_usage':
                    case 'agent_shutdown':
                    case 'system_shutdown':
                        $this->notificationLog($request['name'], $host_id, $rdata);
                        break;
                    default:
                        \Log::warning('Notification receive with unknown reference: '. $request['name']);
                }
            }

            $update_response = $this->hostService->updateHost($host['id'], $host_update_values);
            if (!empty($update_response['error'])) {
                return $update_response;
            }

            $response = $this->prepareResponse($command, $request, $agent_default_interval);

            return ['success' => true, 'response_data' => $response];
        } catch (\Exception $e) {
            \Log::error("Error processing request: " . $e->getMessage());
            return ['error' => 'Internal server error'];
        }
    }

    /**
     * Processes startup data sent by the agent.
     *
     * @param array<string, mixed> $host
     * @param array<string, string|int> $rdata Data sent by the agent.
     * @return array<string, string|int> Updated host values.
     */
    public function processStarting(array $host, array $rdata): array
    {
        if (empty(!$host)) {
            return [];
        }
        \Log::logHost($rdata['log_level'], $host['id'], $rdata['msg'], $rdata['log_type'], $rdata['event_type']);

        // Convert/Decode misc fields if not decoded already
        if (!empty($host['misc']) && !is_array($host['misc'])) {
            $host['misc'] = $this->hostService->decodeMisc($host['misc']);
        }

        $host_update_values = [];

        if (!empty($rdata['ncpu'])) {
            if (!isset($host['misc']['ncpu']) || ($rdata['ncpu'] !== $host['misc']['ncpu'])) {
                $host_update_values['misc']['ncpu'] = $rdata['ncpu'];
            }
        }
        if (!empty($rdata['uptime'])) {
            if (!isset($host['misc']['uptime']) || ($rdata['uptime'] !== $host['misc']['uptime'])) {
                $host_update_values['misc']['uptime'] = $rdata['uptime'];
            }
        }

        return $host_update_values;
    }

    /**
     * Processes statistics sent by the agent.
     *
     * @param int $host_id Host ID.
     * @param array<string, mixed> $rdata Statistics data.
     * @return bool Indicates whether the processing was successful.
     */
    public function processStats(int $host_id, array $rdata): bool
    {
        try {
            if (empty($this->cmdStatsModel)) {
                $this->cmdStatsModel = new CmdStatsModel($this->ctx);
            }

            if (!isset($this->datetimeService)) {
                $this->dateTimeService  = new DateTimeService();
            }

            if (!\isEmpty($rdata['load_avg_stats'])) {
                if (
                    isset($rdata['load_avg_stats']['1min']) &&
                    is_numeric($rdata['load_avg_stats']['1min'])
                ) {
                    $stats_data = [
                        'date' => $this->dateTimeService->dateNow(),
                        'type' => 2,   //loadavg
                        'host_id' => $host_id,
                        'value' => $rdata['load_avg_stats']['5min']
                    ];
                    $this->cmdStatsModel->insertStats($stats_data);
                }
            }

            if (!\isEmpty($rdata['iowait_stats'])) {
                $stats_data = [
                    'date' => $this->dateTimeService->dateNow(),
                    'type' => 3,   //iowait
                    'host_id' => $host_id,
                    'value' => $rdata['iowait_stats']
                ];
                $this->cmdStatsModel->insertStats($stats_data);
            }

            if (!\isEmpty($rdata['mem_stats'])) {
                $stats_data = [
                    'date' => $this->dateTimeService->dateNow(),
                    'type' => 4,   // Memory
                    'host_id' => $host_id,
                    'value' => $rdata['mem_stats']
                ];
                $this->cmdStatsModel->insertStats($stats_data);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Error processing stats: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Processes port information provided by the agent.
     *
     * @param int $host_id Host ID.
     * @param array<string, mixed> $rdata Port data.
     * @return bool Indicates whether the processing was successful.
     */
    public function processPorts(int $host_id, array $rdata): bool
    {
        try {
            if (empty($rdata['listen_ports_info']) || !is_array($rdata['listen_ports_info'])) {
                return false;
            }

            $this->updateListenPorts($host_id, $rdata['listen_ports_info']);

            return true;
        } catch (\Exception $e) {
            \Log::error("Error processing ports: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Updates port information.
     *
     * @param int $host_id Host ID.
     * @param array<int, array<string, mixed>> $listen_ports Ports data.
     * @return bool Indicates success or failure.
     */
    public function updateListenPorts(int $host_id, array $listen_ports): bool
    {
        try {
            $scan_type = 2; // Agent Based

            if (!isset($this->cmdHostModel)) {
                $this->cmdHostModel = new CmdHostModel($this->db);
            }
            if (!isset($this->datetimeService)) {
                $this->dateTimeService  = new DateTimeService();
            }
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
                        $warnmsg = "Service name change detected on $interface "
                            . "({$db_port['service']}->{$port['service']}) ({$pnumber}) ({$ip_version})";
                        # Do not alert on localhost ports
                        if (strpos($interface, '127.') === 0 || strpos($interface, '[::') === 0) {
                            \Log::logHost(\LogLevel::NOTICE , $host_id, $warnmsg, \LogType::EVENT, \EventType::SERVICE_NAME_CHANGE);
                        } else {
                            $this->hostService->setWarnOn(
                                $host_id,
                                $warnmsg,
                                \LogType::EVENT_WARN,
                                \EventType::SERVICE_NAME_CHANGE
                            );
                        }
                        $this->hostService->updatePort($db_port['id'], [
                            "service" => $port['service'],
                            "online" => 1,
                            "last_check" => $this->dateTimeService->dateNow(),
                        ]);
                    } elseif ((int)$db_port['online'] === 0) {
                        $alertmsg = "Port UP detected on $interface ({$port['service']}) ($pnumber)($ip_version)";
                        if (strpos($interface, '127.') === 0 || strpos($interface, '[::') === 0) {
                            \Log::logHost(\LogLevel::NOTICE , $host_id, $alertmsg, \LogType::EVENT, \EventType::PORT_UP_LOCAL);
                        } else {
                            $this->hostService->setWarnOn($host_id, $alertmsg, \LogType::EVENT_WARN, \EventType::PORT_UP);
                        }

                        $this->hostService->updatePort($db_port['id'], [
                            "online" => 1,
                            "last_check" => $this->dateTimeService->dateNow(),
                        ]);
                    }
                    unset($db_ports_map[$key]);
                } else {
                    $new_port_data = [
                        'hid' => $host_id,
                        'scan_type' => $scan_type,
                        'protocol' => $protocol,
                        'pnumber' => $pnumber,
                        'online' => 1,
                        'service' => $port['service'],
                        'interface' => $interface,
                        'ip_version' => $ip_version,
                        'last_check' => $this->dateTimeService->dateNow(),
                    ];
                    $this->cmdHostModel->addPort($new_port_data);
                    $log_msg = "New port detected on $interface $pnumber ({$port['service']})($ip_version)";

                    # Do not alert on localhost ports
                    if (strpos($interface, '127.') === 0 || strpos($interface, '[::') === 0) {
                        \Log::logHost(\LogLevel::NOTICE , $host_id, $log_msg, \LogType::EVENT, \EventType::PORT_NEW_LOCAL);
                    } else {
                        $this->hostService->setAlertOn($host_id, $log_msg, \EventType::PORT_NEW, \LogType::EVENT_ALERT);
                    }


                    unset($db_ports_map[$key]);
                }
            }

            foreach ($db_ports_map as $db_port) {
                if ((int)$db_port['online'] === 1) {
                    $set = [
                        'online' => 0,
                        'last_check' => $this->dateTimeService->dateNow(),
                    ];
                    $log_msg = "Port DOWN detected on {$interface} {$db_port['pnumber']} ({$db_port['service']})";
                    if (strpos($interface, '127.') === 0 || strpos($interface, '[::') === 0) {
                        \Log::logHost(\LogLevel::NOTICE , $host_id, $log_msg, \LogType::EVENT, \EventType::PORT_DOWN_LOCAL);
                    } else {
                        $this->hostService->setAlertOn($host_id, $log_msg, \LogType::EVENT_ALERT, \EventType::PORT_DOWN);
                    }
                    $this->hostService->updatePort($db_port['id'], $set);
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Error updating listen ports: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handles ping data provided by the agent.
     *
     * @param int $host_id Host ID.
     * @param array<string, string|int> $host_update_values Fields to update.
     * @param array<string, mixed> $rdata Data from the agent.
     * @return array<string, string|int> Additional values to update.
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
            foreach ($rdata['host_logs'] as $hlog) {
                \Log::logHost($hlog['level'], $host_id, 'Agent: ' . $hlog['message']);
            }
        endif;

        return $host_update_values;
    }

    /**
     * Prepares a response for the agent.
     *
     * @param string $command Command name.
     * @param array<string, mixed> $request Request data.
     * @param int $interval Refresh interval.
     * @return array<string, string|int> Response data.
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
     * Validates the agent's request.
     *
     * @param array<string, mixed> $host Host data.
     * @param string $token Authentication token.
     * @param int $host_id Host ID.
     * @return array<string, string|int> Result array indicating success or error.
     */
    private function validateHostRequest(array $host, string $token, int $host_id): array
    {
        if (!$host) {
            $error_msg = 'Host not found, requested id:' . $host_id;
        }
        if (empty($host['token']) || $host['token'] !== $token) {
            $msg = 'Invalid Token receive from id:' . $host_id;
        }
        if (empty($host['ip'])) {
            $msg = "Invalid Token receive from id:" . $host_id;
        }

        $remote_ip = Filter::getRemoteIp();
        if ($host['ip'] != $remote_ip ) {
            $msg = "Empty or wrong ip receive, expected {$host['ip']} receive $remote_ip";
        }

        if (!empty($error_msg)) {
            \Log::error($msg);
            return ['error' => $msg];
        }

        return ['success' => true];
    }

    /**
     * Gets the default interval for the agent.
     *
     * @return int Interval in seconds.
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
     * Prepares the host's update values.
     *
     * @param array<string, mixed> $host Current host data.
     * @param array<string, mixed> $request Request data.
     * @param int $interval Update interval.
     * @return array<string, string|int> Prepared update values.
     */
    private function prepareHostUpdateValues(array $host, array $request, int $interval): array
    {
        $values['misc'] = [
            'agent_next_report' => time() + (int) $interval,
            'agent_last_contact' => time(),
            'agent_online' => 1
        ];

        // Convert/Decode misc fields if not decoded already
        if (!empty($host['misc']) && !is_array($host['misc'])) {
            $host['misc'] = $this->hostService->decodeMisc($host['misc']);
        }

        if (
            !empty($request['version']) &&
            (
                empty($host['misc']['agent_version']) ||
                ($host['misc']['agent_version'] != (string) $request['version'])
            )
        ) {
            $values['misc']['agent_version'] = (string) $request['version'];
        }

        if ((int)$host['online'] !== 1) {
            $values['online'] = 1;
        }
        if ((int)$host['agent_online'] !== 1) {
            $values['agent_online'] = 1;
        }

        if (!isset($this->datetimeService)) {
            $this->dateTimeService  = new DateTimeService();
        }
        $dateNow = $this->dateTimeService->dateNow();
        $values['last_check'] = $dateNow;
        $values['last_seen'] = $dateNow;

        return $values;
    }

    /**
     * Logs a notification.
     *
     * @param string $request_name Name of the request.
     * @param int $host_id Host ID.
     * @param array<string, mixed> $rdata Notification data.
     * @return void
     */
    private function notificationLog(string $request_name, int $host_id, array $rdata): void
    {
        $event_type = !empty($rdata['event_type']) ? $rdata['event_type'] : 0;
        $log_type = isset($rdata['log_type'])
            ? $rdata['log_type']
            : (!empty($rdata['event_type']) ? \LogType::EVENT : 0);
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
