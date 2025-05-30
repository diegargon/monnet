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

use App\Core\AppContext;
use App\Core\DBManager;
use App\Core\ConfigService;

use App\Services\HostService;
use App\Services\DateTimeService;
use App\Services\LogSystemService;
use App\Services\LogHostsService;
use App\Services\Filter;

use App\Models\CmdHostModel;
use App\Models\CmdStatsModel;

class FeedMeService
{
    /**
     * @var AppContext $ctx Context
     */
    private AppContext $ctx;

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
     * @var ConfigService $ncfg Config
     */
    private ConfigService $ncfg;

    /**
     * @var DBManager
     */
    private DBManager $db;

    /** @var DateTimeService */
    private DateTimeService $dateTimeService;

    /** @var LogSystemService */
    private LogSystemService $logSys;

    /** @var LogHostsService */
    private LogHostsService $logHost;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get(ConfigService::class);
        $this->db = $ctx->get(DBManager::class);
        $this->hostService = new HostService($ctx);
        $this->logSys = new LogSystemService($ctx);
        $this->logHost = new LogHostsService($ctx);
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
        $data_reply = [];
        try {
            $command = $request['cmd'];
            $host_id = (int) $request['id'];
            $host = $this->hostService->getHostById($host_id);
            if (!$host) {
                return ['error' => 'Agent Host not found in database. deleted? (' . $host_id . ')'];
            }
            if (!empty($host['misc'])) {
                $host['misc'] = $this->hostService->decodeMisc($host['misc']);
            }

            $rdata = $request['data'];
            $host_update_values = [];

            $validated_response = $this->validateHostRequest($host, $request['token'], $host_id);
            if (!empty($validated_response['error'])) {
                return $validated_response;
            }

            $agent_default_interval = $this->getAgentInterval();

            $host_update_values = $this->prepareHostUpdateValues($host, $request, $agent_default_interval);

            if (!empty($request['name'])) {
                switch ($request['name']) {
                    case 'ping': //Ping come with real time data
                        $ping_updates = $this->processPingData($host_id, $host_update_values, $rdata);
                        if (!empty($ping_updates)) {
                            $host_update_values = array_merge($host_update_values, $ping_updates);
                        }
                        if (!empty($host['misc']['agent_config_update'])) {
                            $data_reply['config'] = $this->build_agent_config($host);
                            # New config send disable flag
                            $host_update_values['misc']['agent_config_update'] = 0;
                        }

                        # Send ips to agent same network to discovery macs
                        if ($host['misc']['agent_version'] >= 0.199) {
                            if (!empty($host['network']) && $this->shouldCheckMacs((string)$host['network'])) {
                                $hosts_mac_check = $this->hostService->getHostsWithMacCheckByNetwork((int)$host['network']);
                                $data_reply['check_macs'] = array_column($hosts_mac_check, 'ip');
                            }
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
                        $this->logSys->warning('Notification receive with unknown reference: '. $request['name']);
                }
            }

            $update_response = $this->hostService->updateHost($host['id'], $host_update_values);
            if (!empty($update_response['error'])) {
                return $update_response;
            }

            $response = $this->prepareResponse($command, $request, $agent_default_interval, $data_reply);

            return ['success' => true, 'response_data' => $response];
        } catch (\Exception $e) {
            $this->logSys->error("Error processing request: " . $e->getMessage());
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
        $this->logHost->logHost($rdata['log_level'], $host['id'], $rdata['msg'], $rdata['log_type'], $rdata['event_type']);

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
        # Stats Table receiven every X minutes

        try {
            if (empty($this->cmdStatsModel)) {
                $this->cmdStatsModel = new CmdStatsModel($this->ctx);
            }

            if (!isset($this->datetimeService)) {
                $this->dateTimeService  = new DateTimeService();
            }

            if (!empty($rdata['load_avg_stats'])) {
                if (
                    isset($rdata['load_avg_stats']['5min']) &&
                    is_numeric($rdata['load_avg_stats']['5min'])
                ) {
                    $stats_data = [
                        'date' => DateTimeService::dateNow(),
                        'type' => 2,   //loadavg
                        'host_id' => $host_id,
                        'value' => $rdata['load_avg_stats']['5min']
                    ];
                    $this->cmdStatsModel->insertStats($stats_data);
                }
            }

            if (!empty($rdata['iowait_stats'])) {
                $stats_data = [
                    'date' => DateTimeService::dateNow(),
                    'type' => 3,   //iowait
                    'host_id' => $host_id,
                    'value' => $rdata['iowait_stats']
                ];
                $this->cmdStatsModel->insertStats($stats_data);
            }

            if (!empty($rdata['mem_stats'])) {
                $stats_data = [
                    'date' => DateTimeService::dateNow(),
                    'type' => 4,   # Memory
                    'host_id' => $host_id,
                    'value' => $rdata['mem_stats']
                ];
                $this->cmdStatsModel->insertStats($stats_data);
            }

            return true;
        } catch (\Exception $e) {
            $this->logSys->error("Error processing stats: " . $e->getMessage());
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
            $this->logSys->error("Error processing ports: " . $e->getMessage());
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
            $scan_type = 2; # Agent Based

            if (!isset($this->cmdHostModel)) {
                $this->cmdHostModel = new CmdHostModel($this->db);
            }
            if (!isset($this->datetimeService)) {
                $this->dateTimeService  = new DateTimeService();
            }
            $db_host_ports = $this->cmdHostModel->getHostScanPorts($host_id, $scan_type);
            $db_ports_map = [];

            # Normalize TODO: to method?
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

                    if (
                        $db_port['service'] !== $port['service'] &&
                        !$this->isServiceNameEquivalent($db_port['service'], $port['service'])
                    ) {
                        $warnmsg = "Service name change detected on $interface "
                            . "({$db_port['service']}->{$port['service']}) ({$pnumber}) ({$ip_version})";
                        # Do not alert on localhost ports
                        if (strpos($interface, '127.') === 0 || strpos($interface, '[::') === 0) {
                            $this->logHost->logHost(\LogLevel::NOTICE , $host_id, $warnmsg, \LogType::EVENT, \EventType::SERVICE_NAME_CHANGE);
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
                            "last_check" => DateTimeService::dateNow(),
                        ]);
                    }

                    if ((int)$db_port['online'] === 0) {
                        $alertmsg = "Port UP detected on $interface ({$port['service']}) ($pnumber)($ip_version)";
                        if (strpos($interface, '127.') === 0 || strpos($interface, '[::') === 0) {
                            $this->logHost->logHost(\LogLevel::NOTICE , $host_id, $alertmsg, \LogType::EVENT, \EventType::PORT_UP_LOCAL);
                        } else {
                            $this->hostService->setWarnOn($host_id, $alertmsg, \LogType::EVENT_WARN, \EventType::PORT_UP);
                        }

                        $this->hostService->updatePort($db_port['id'], [
                            "online" => 1,
                            "last_check" => DateTimeService::dateNow(),
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
                        'last_check' => DateTimeService::dateNow(),
                    ];
                    $this->cmdHostModel->addPort($new_port_data);
                    $log_msg = "New port detected on $interface $pnumber ({$port['service']})($ip_version)";

                    # Do not alert on localhost ports
                    if (strpos($interface, '127.') === 0 || strpos($interface, '[::') === 0) {
                        $this->logHost->logHost(\LogLevel::NOTICE , $host_id, $log_msg, \LogType::EVENT, \EventType::PORT_NEW_LOCAL);
                    } else {
                        $this->hostService->setAlertOn($host_id, $log_msg, \LogType::EVENT_ALERT, \EventType::PORT_NEW);
                    }
                    unset($db_ports_map[$key]);
                }
            }

            foreach ($db_ports_map as $db_port) {
                if ((int)$db_port['online'] === 1) {
                    $set = [
                        'online' => 0,
                        'last_check' => DateTimeService::dateNow(),
                    ];
                    $log_msg = "Port DOWN detected on {$interface} {$db_port['pnumber']} ({$db_port['service']})";
                    if (strpos($interface, '127.') === 0 || strpos($interface, '[::') === 0) {
                        $this->logHost->logHost(\LogLevel::NOTICE , $host_id, $log_msg, \LogType::EVENT, \EventType::PORT_DOWN_LOCAL);
                    } else {
                        $this->hostService->setAlertOn($host_id, $log_msg, \LogType::EVENT_ALERT, \EventType::PORT_DOWN);
                    }
                    $this->hostService->updatePort($db_port['id'], $set);
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->logSys->error("Error updating listen ports: " . $e->getMessage());
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
        # These values are using for realtime
        if (!empty($rdata['loadavg'])) {
            $host_update_values['misc']['load_avg'] = serialize($rdata['loadavg']);
        }
        if (!empty($rdata['meminfo'])) {
            $host_update_values['misc']['mem_info'] = serialize($rdata['meminfo']);
        }
        if (!empty($rdata['disksinfo'])) {
            $host_update_values['misc']['disks_info'] = serialize($rdata['disksinfo']);
        }
        if (!empty($rdata['iowait'])) {
            $host_update_values['misc']['iowait'] = $rdata['iowait'];
        }
        if (!empty($rdata['host_logs'])) {
            foreach ($rdata['host_logs'] as $hlog) {
                $this->logHost->logHost($hlog['level'], $host_id, '[Agent]: ' . $hlog['message']);
            }
        }
        # Update macs asked to and receive from an agent
        if (!empty($rdata['collect_macs'])) {
            if (is_array($rdata['collect_macs'])) {
                foreach ($rdata['collect_macs'] as $mac_entry) {
                    # Do not check for mac null since updateMacByIp set the mac_check
                    if (!empty($mac_entry['ip'])) {
                        $this->hostService->updateMacByIp($mac_entry['ip'], $mac_entry['mac']);
                    }
                }
            }
        }

        return $host_update_values;
    }

    /**
     * Prepares a response for the agent.
     *
     * @param string $command Command name.
     * @param array<string, mixed> $request Request data.
     * @param int $interval Refresh $interval.
     * @param array<string, mixed> $data
     * @return array<string, string|int> Response data.
     */
    private function prepareResponse(string $command, array $request, int $interval, array $reply_data = []): array
    {
        $response = [
            'cmd' => $command,
            'token' => $request['token'],
            'version' => $this->ncfg->get('agent_min_version'),
            'response_msg' => null,
            'refresh' => $interval,
            'data' => $reply_data,
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
        if (empty($host['token']) || $host['token'] !== $token) {
            $msg = 'Invalid Token receive from id:' . $host_id;
        }
        if (empty($host['ip'])) {
            $msg = "Invalid Token receive from id:" . $host_id;
        }

        # TODO 127.0.0.1 and proxys should be allowed
        #$remote_ip = Filter::getRemoteIp();
        #if ($host['ip'] != $remote_ip &&  $remote_ip !== '127.0.0.1') {
        #    $msg = "Empty or wrong ip receive, expected {$host['ip']} receive $remote_ip";
        #}

        if (!empty($msg)) {
            $this->logSys->error($msg);
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

        if (
            !empty($request['version']) &&
            (
                empty($host['misc']['agent_version']) ||
                ($host['misc']['agent_version'] != (string) $request['version'])
            )
        ) {
            $values['misc']['agent_version'] = (string) $request['version'];
        }

        # if we receive we assume...
        if ((int)$host['online'] !== 1) {
            $values['online'] = 1;
        }
        if ((int)$host['agent_online'] !== 1) {
            $values['agent_online'] = 1;
        }

        if (empty($host['agent_installed'])) {
            $values['agent_installed'] = 1;
        }

        if (!isset($this->datetimeService)) {
            $this->dateTimeService  = new DateTimeService();
        }
        $date_now = DateTimeService::dateNow();
        $values['last_check'] = $date_now;
        $values['last_seen'] = $date_now;

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
        $log_msg = "[Agent] $request_name";
        isset($rdata['msg']) ? $log_msg .= ': ' . $rdata['msg'] : null;

        if (!empty($rdata['event_value'])) {
            $log_msg .= ' Event value: ' . $rdata['event_value'];
        }

        if ($log_level <= \LogLevel::CRITICAL) {
            $this->hostService->setAlertOn($host_id, $log_msg, \LogType::EVENT_ALERT, $event_type);
        } elseif ($log_level == \LogLevel::ERROR || $log_level == \LogLevel::WARNING) {
            $this->hostService->setWarnOn($host_id, $log_msg, \LogType::EVENT_WARN, $event_type);
        } else {
            $this->logHost->logHost($log_level, $host_id, $log_msg, $log_type, $event_type);
        }
    }

    private function build_agent_config(array $host): array
    {
        $misc = $host['misc'];

        $agent_config_keys = [
            'agent_log_level',
            'mem_alert_threshold',
            'mem_warn_threshold',
            'disks_alert_threshold',
            'disks_warn_threshold',
        ];

        $config = [];

        foreach ($agent_config_keys as $key) {
            if (!empty($misc[$key])) {
                $config[$key] = $misc[$key];
            }
        }

        return $config;
    }

    /**
     * Determines if a service name change should be ignored
     *
     * @param string $oldService
     * @param string $newService
     * @return bool
     */
    private function isServiceNameEquivalent(string $oldService, string $newService): bool
    {
        $serviceGroups = [
            // Postfix
            ['master', 'postfix', 'smtp', 'smtpd', 'mail'],
            // Dovecot
            ['dovecot', 'imap', 'pop3', 'pop3-login', 'imap-login', 'lmtp', 'anvil'],
            // Amavis/SpamAssassin
            ['amavis', 'amavisd', 'spamassassin', 'spamd', 'spamd child', 'perl'],
            // Proxmox
            ['postscreen', 'master'],
            // ClamAV
            ['clamd', 'clamav', 'clamav-milter'],
            // systemd-resolved
            ['systemd-resolved', 'resolved'],
            // OpenDKIM/OpenDMARC
            ['opendkim', 'opendmarc'],
            // Courier
            ['courier-imap', 'courier-pop', 'courier'],
        ];

        foreach ($serviceGroups as $group) {
            if (in_array($oldService, $group, true) && in_array($newService, $group, true)) {
                return true;
            }
        }
        return false;
    }

    private function shouldCheckMacs(string $networkId, int $intervalSeconds = 3600): bool
    {
        $configKey = 'last_mac_check_network_' . $networkId;
        $lastRun = (int) $this->ncfg->get($configKey, 0);
        $now = time();

        if (($now - $lastRun) < $intervalSeconds) {
            return false;
        }

        $this->ncfg->set($configKey, $now, 1);
        return true;
    }
}
