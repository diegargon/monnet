<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
/**
 *
 * @param AppContext $ctx
 * @return bool
 */
function check_known_hosts(AppContext $ctx): bool
{
    $ping_known_time = microtime(true);

    $lng = $ctx->get('lng');

    /** @var Database $db */
    $db = $ctx->get('Mysql');

    /** @var Hosts $hosts */
    $hosts = $ctx->get('Hosts');

    /** @var Config $ncfg */
    $ncfg = $ctx->get('Config');

    if (!is_object($hosts)) {
        Log::error("hosts is not a object");
        return false;
    }

    Log::debug('Pinging known host');
    $db_hosts = $hosts->getknownEnabled();

    $retries = $ncfg->get('check_retries');

    foreach ($db_hosts as $host) {
        $new_host_status = [];
        /* Port Scan */
        if ($host['check_method'] == 2) { //Ports
            Log::debug("Pinging host ports {$host['ip']}");
            $check_ports_result = check_host_ports($ctx, $host);

            // If host change status to off  we check again
            if (
                    $host['online'] == 1 &&
                    $check_ports_result['online'] == 0 &&
                    !isset($check_ports_result['no_retry'])
            ) {
                for ($i = 2; $i <= $retries; $i++) {
                    usleep($ncfg->get('check_retries_usleep'));
                    $check_ports_result = check_host_ports($ctx, $host);

                    if ($check_ports_result['online'] == 1) {
                        Log::debug("Retry $i port check works for {$host['display_name']}");
                        break;
                    } elseif ($i === $retries) {
                        Log::debug("Retry $i ping port not work for {$host['display_name']}");
                    }
                }
            }
            if ($check_ports_result['online'] == 0) {
                /*
                 * All Ports down or not specified. Check with ping
                 */
                if (!empty($host['timeout']) && $host['timeout'] > 0.0) {
                    $sec = intval($host['timeout']);
                    $usec = ($host['timeout'] - $sec);
                    $usec = $usec > 0 ? $usec * 1000000 : 0;
                } else {
                    $sec = 0;
                    $usec = $ncfg->get('ping_hosts_timeout');
                }
                $host_ping = ping($host['ip'], ['sec' => $sec, 'usec' => $usec]);
                if ($host_ping['online']) {
                    //Ports down but we got ping
                    $check_ports_result['online'] = 1;
                    $check_ports_result['latency'] = $host_ping['latency'];
                } else {
                    if (!empty($host['misc']['disable_alarms'])) {
                        $event_type = LogType::EVENT;
                        $check_ports_result['warn'] = 0;
                    } else {
                        $event_type = LogType::EVENT_ALERT;
                    }
                    Log::logHost(
                        LogLevel::ALERT,
                        $host['id'],
                        'All Ports down / No ping response',
                        $event_type,
                        EventType::HOST_BECOME_OFF
                    );
                }
            }

            /* Checks finished */

            /* Port Status Change Logging */
            if (isset($check_ports_result['ports']) && is_array($check_ports_result['ports'])) {
                $ports_status = $check_ports_result['ports'];
                foreach ($ports_status as $port) :
                    if ($port['old_online_status'] == 0 && $port['online'] == 1) {
                        Log::logHost(
                            LogLevel::NOTICE,
                            $host['id'],
                            "Port {$port['pnumber']} become Online",
                            LogType::EVENT,
                            EventType::PORT_UP
                        );
                    } elseif ($port['old_online_status'] == 1 && $port['online'] == 0) {
                        if (in_array($check_ports_result['error_code'], [58, 59, 60], true)) {
                            $event_type = EventType::CERT_ERROR;
                        } else {
                            $event_type = EventType::PORT_DOWN;
                        }

                        $log_msg = "Port {$port['pnumber']} down:";
                        $log_msg .= " {$check_ports_result['error_msg']}";
                        $log_msg .= " ({$check_ports_result['error_code']})";
                        if (empty($host['misc']['alarm_port_disable'])) {
                            Log::logHost(
                                LogLevel::WARNING,
                                $host['id'],
                                $log_msg,
                                LogType::EVENT_WARN,
                                $event_type
                            );
                        } else {
                            //No warn sign
                            $check_ports_result['warn'] = 0;
                            Log::logHost(
                                LogLevel::WARNING,
                                $host['id'],
                                $log_msg,
                                LogType::EVENT,
                                $event_type
                            );
                        }
                    }
                endforeach;
            }

            /* Update Ports data */
            $new_host_status = [
                'online' => $check_ports_result['online'],
                'warn' =>  $check_ports_result['warn'],
                'latency' => $check_ports_result['latency']
            ];
            $ports_status = $check_ports_result['ports'];
            foreach ($ports_status as $portid => $new_port_status) :
                unset($new_port_status['old_online_status']);
                unset($new_port_status['error_msg']);
                unset($new_port_status['error_code']);
                //TODO Alarm if port changes
                //if (!empty($host['misc']['alarm_port_email'])) :
                //    $hosts->sendHostMail($host['id'], $log_msg);
                //endif;
                $db->update('ports', $new_port_status, ['id' => $portid]);
            endforeach;
        } else {
            /* Ping Scan */
            if (!empty($host['disable_ping'])) {
                continue;
            }
            Log::debug("Pinging {$host['ip']}");
            $ping_host_result = ping_known_host($ctx, $host);
            //recheck if was online
            if ($host['online'] == 1 && $ping_host_result['online'] == 0) {
                for ($i = 2; $i <= $retries; $i++) :
                    usleep($ncfg->get('check_retries_usleep'));
                    $ping_host_result = ping_known_host($ctx, $host);

                    if ($ping_host_result['online'] == 1) {
                        Log::debug("Retry $i ping works for {$host['display_name']}");
                        break;
                    } elseif ($i === $retries) {
                        Log::debug("Retry $i ping not work for {$host['display_name']}");
                    }
                endfor;
            }
            (valid_array($ping_host_result)) ? $new_host_status = $ping_host_result : null;
        }

        /*  Update host with scan data */

        if (valid_array($new_host_status)) {
            if ($new_host_status['online'] && empty($host['mac'])) {
                $mac = get_mac($host['ip']);
                $new_host_status['mac'] = !empty($mac) ? $mac : null;
            }
            if ($host['online'] == 0 && $new_host_status['online'] == 1) {
                $new_host_status['glow'] = date_now(); //Glow Time Mark
                $log_msg = $lng['L_HOST_BECOME_ON'];
                Log::logHost(LogLevel::NOTICE, $host['id'], $log_msg, LogType::EVENT, EventType::HOST_BECOME_ON);

                //Try get hostname when a host become on
                $hostname = $hosts->getHostname($host['ip']);
                if ($hostname && $hostname !== $host['hostname'] && $hostname !== $host['ip']) :
                    $new_host_status['hostname'] = $hostname;
                endif;
            } elseif ($host['online'] == 1 && $new_host_status['online'] == 0) {
                $new_host_status['glow'] = date_now(); //Glow Time Mark
                //$host_timeout = !empty($host['timeout']) ? '(' . $host['timeout'] . ')' : '';
                $log_msg = $lng['L_HOST_BECOME_OFF'];
                // Create alert when always on is set
                if (!empty($host['misc']['always_on'])) :
                    $hosts->setAlertOn($host['id'], $log_msg, LogType::EVENT_ALERT, EventType::SYSTEM_SHUTDOWN);
                else :
                    Log::logHost(LogLevel::NOTICE, $host['id'], $log_msg, LogType::EVENT, EventType::SYSTEM_SHUTDOWN);
                endif;

                if (!empty($host['misc']['email_alarms']) && !empty($host['misc']['alarm_ping_email'])) :
                    $hosts->sendHostMail($host['id'], $log_msg);
                endif;
            }

            $hosts->update($host['id'], $new_host_status);
            if ($new_host_status['online'] == 1 && isset($new_host_status['latency'])) {
                $ping_latency = $new_host_status['latency'];
                $set_ping_stats = [
                    'date' => date_now(),
                    'type' => 1,
                    'host_id' => $host['id'],
                    'value' => $ping_latency
                ];
                $db->insert('stats', $set_ping_stats);
            }
        } else {
            Log::warning("Known host ping status error {$host['id']}:{$host['display_name']}");
        }
    }
    Log::debug('Finish ping known host, took ' . (intval(microtime(true) - $ping_known_time)) . ' seconds');

    return true;
}

/**
 * Ping... nets
 * @param AppContext $ctx
 */
function ping_nets(AppContext $ctx): void
{
    $hosts = $ctx->get('Hosts');
    $networks = $ctx->get('Networks');
    $ping_net_time = microtime(true);
    $db_hosts = $hosts->getAll();
    $iplist = $networks->buildIpScanList();

    //We remove known hosts since we checked in other functions
    foreach ($iplist as $kip => $vip) :
        $iplist[$kip] = trim($vip);
        foreach ($db_hosts as $host) :
            if ($host['ip'] == $vip) {
                unset($iplist[$kip]);
            }
        endforeach;
    endforeach;

    foreach ($iplist as $ip) :
        ping_nets_ip($ctx, $ip);
    endforeach;

    Log::debug('Ping net took ' . (intval(microtime(true) - $ping_net_time)) . ' seconds');
}

/**
 *
 * @param AppContext $ctx
 * @param string $ip
 */
function ping_nets_ip(AppContext $ctx, string $ip)
{
    $hosts = $ctx->get('Hosts');
    $networks = $ctx->get('Networks');
    $ncfg = $ctx->get('Config');

    $timeout = ['sec' => 0, 'usec' => $ncfg->get('ping_nets_timeout')];

    $latency = microtime(true);
    $ip_status = ping($ip, $timeout);
    $set = [];

    if ($ip_status['online']) :
        //New host
        $mac = get_mac($ip);
        if ($mac) {
            $set['mac'] = trim($mac);
            $mac_info = get_mac_vendor($mac);
            $set['mac_vendor'] = (!empty($mac_info['company'])) ? $mac_info['company'] : '-';
        }
        $set['ip'] = $ip;
        $set['online'] = 1;
        $set['warn'] = 1;

        $network_id = $networks->getNetworkIDbyIP($ip);
        if ($network_id == false) {
            Log::warning('Failed to get id network for ip ' . $ip);
            // 1 is default
            $set['network'] = 1;
        } else {
            $set['network'] = $network_id;
        }

        $set['latency'] = round_latency($latency);
        $set['last_seen'] = date_now();
        $hostname = $hosts->getHostname($ip);
        !empty($hostname) && ($hostname != $ip) ? $set['hostname'] = $hostname : null;
        $hosts->insert($set);
    endif;
}
/**
 *
 * @param Hosts $hosts
 * @param int $forceall
 */
function fill_hostnames(Hosts $hosts, int $forceall = 0): void
{
    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) :
        if (empty($host['hostname']) || $forceall === 1) {
            //Log::debug("Getting hostname {$host['ip']}");
            $hostname = $hosts->getHostname($host['ip']);
            if ($hostname !== false && $hostname != $host['ip']) {
                $update['hostname'] = $hostname;
                $hosts->update($host['id'], $update);
            }
        }
    endforeach;
}

/**
 *
 * @param Hosts $hosts
 * @param int $forceall
 */
function fill_mac_vendors(Hosts $hosts, int $forceall = 0): void
{
    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) :
        $vendor = [];
        $update = [];

        if (
            (!empty($host['mac'])) &&
            (empty($host['misc']['mac_vendor']) || $forceall === 1)
        ) {
            Log::debug("Getting mac vendor for {$host['display_name']}");
            $vendor = get_mac_vendor_local(trim($host['mac']));
            if (empty($vendor)) :
                Log::debug("Local lookup fail, checking mac online");
                $vendor = get_mac_vendor(trim($host['mac']));
            endif;

            if (empty($vendor['company'])) :
                Log::debug("Mac vendor for {$host['mac']} is null");
                (empty($host['misc']['mac_vendor'])) ? $update['misc']['mac_vendor'] = '-' : null;
            else :
                if (!empty($host['misc']['mac_vendor']) && ($vendor['company'] != $host['misc']['mac_vendor'])) :
                    Log::warning("Mac vendor change from {$host['misc']['mac_vendor']} to {$vendor['company']} ] updating...");
                endif;
                $update['mac_vendor'] = $vendor['company'];
            endif;
            if (!empty($update) && ($host['misc']['mac_vendor'] != $update['mac_vendor'])) :
                $hosts->update($host['id'], $update);
            endif;
        }
    endforeach;
}

/**
 *
 * @param Hosts $hosts
 * @return void
 */
function check_macs(Hosts $hosts): void
{
    $known_hosts = $hosts->getknownEnabled();

    Log::info('Checking macs');
    foreach ($known_hosts as $host) :
        $new_mac = get_mac($host['ip']);
        if (!empty($new_mac) && $host['mac'] != $new_mac) :
            $update['mac'] = trim($new_mac);
            $hosts->update($host['id'], $update);
        endif;
    endforeach;
}

/**
 * Scan Host Ports
 * protocol = 2 (udp) only work for non DGRAM sockets, dgram need wait for response/ ping
 * @param AppContext $ctx
 * @param array<string, mixed> $host
 * @return array<string, mixed>
 */
function check_host_ports(AppContext $ctx, array $host): array
{
    $ncfg = $ctx->get('Config');

    $latencies = [];

    $host_result = [
        'online' => 0,
        'latency' => null,
        'warn' => 0,
        'ports' => [],
    ];
    !empty($host['warn']) ? $host_result['warn'] = 1 : null;

    $networks = $ctx->get('Networks');
    $db = $ctx->get('Mysql');

    $result = $db->selectAll('ports', ['hid' => $host['id'], 'scan_type' => 1 ]);
    $ports = $db->fetchAll($result);

    if (empty($ports)) {
         Log::warning("Port checking is on but not ports on {$host['id']}:{$host['display_name']} pinging host.");
         // No retry and  Online to 0 to force ping test
         return ['online' => 0, 'no_retry' => 1];
    }

    if (!empty($host['timeout'])) {
        $timeout = $host['timeout'];
    } else {
        $timeout = $networks->isLocal($host['ip']) ? $ncfg->get('port_timeout_local') : $ncfg->get('port_timeout');
    }

    foreach ($ports as $port) :
        $conn = null;
        $https = $selfSigned = false;
        $error_code = $error_msg = '';
        $port_status = [];
        $port_status['last_check'] = date_now();
        $port['protocol'] = (int) $port['protocol'];
        $tim_start = microtime(true);
        $ip = trim($host['ip']);
        $port_status['old_online_status'] = $port['online'];
        $port_status['pnumber'] = $port['pnumber'];

        if ($port['protocol'] === 2) {
            $ip = 'udp://' . $ip;
        } elseif ($port['protocol'] === 3 || $port['protocol'] === 4) {
            /*
             *  (3) HTTPS check cert
             *  (4) HTTPS SS Not drop error with self-signed cert
             */
            $https = true;
            $ip = 'https://';
            !empty($host['hostname']) ? $ip .= $host['hostname'] : $ip .= $ip;
            ($port['protocol'] === 4) ? $selfSigned = true : null;
        } elseif ($port['protocol'] === 5) {
            $ip = 'http://' . $ip;
        }

        if ($port['protocol'] > 2) {
            if ($https && ((int) $port['pnumber'] !== 443)) {
                $ip = $ip . ':' . $port['pnumber'];
            }
            if (!$https && (int) $port['pnumber'] !== 80) {
                $ip = $ip . ':' . $port['pnumber'];
            }
            $response = curl_check_webport($ip, $https, $selfSigned, $timeout);
            if (
                $response !== false &&
                $response['http_code'] >= 200 &&
                $response['http_code'] < 400
            ) {
                $conn = true;
            } else {
                $error_code = $response['errno'];
                $error_msg = $response['error'];
                $conn = false;
            }
        } elseif ($port['protocol'] === 1 || $port['protocol'] === 2) {
            $conn = @fsockopen($ip, $port['pnumber'], $error_code, $error_msg, $timeout);
            $host_result['error_code'] = $error_code;
            $host_result['error_msg'] = $error_msg;
        }

        if (is_resource($conn) || $conn === true) {
            $host_result['online'] = 1; // Host is online
            $port_status['online'] = 1; // Port is Online
            $latencies[] = round_latency(microtime(true) - $tim_start);
            (!is_bool($conn)) ? fclose($conn) : null;
        } else {
            $port_status['online'] = 0;
            $port_status['error_msg'] = $error_msg;
            $port_status['error_code'] = $error_code;
            $host_result['warn'] = 1;
        }

        $host_result['ports'][$port['id']] = $port_status;
    endforeach;

    if ($host_result['online'] === 1) {
        // Calculamos la media latencia puertos
        if (count($latencies) > 0) {
            $average_latency = array_sum($latencies) / count($latencies);
            $host_result['latency'] = $average_latency;
        }
    }

    return $host_result;
}

/**
 *
 * @param AppContext $ctx
 * @param array<string, mixed> $host
 * @return array<string,string|int>
 */
function ping_known_host(AppContext $ctx, array $host): array
{
    $ncfg = $ctx->get('Config');

    $time_now = date_now();
    $networks = $ctx->get('Networks');

    if (!empty($host['timeout']) && $host['timeout'] > 0.0) {
        $sec = intval($host['timeout']);
        $usec = ($host['timeout'] - $sec);
        $usec = $usec > 0 ? $usec * 1000000 : 0;
    } elseif ($networks->isLocal($host['ip'])) {
        $sec = 0;
        $usec = $ncfg->get('ping_local_hosts_timeout');
    } else {
        $sec = 0;
        $usec = $ncfg->get('ping_hosts_timeout');
    }

    $timeout = ['sec' => $sec, 'usec' => $usec];

    $ip_status = ping($host['ip'], $timeout);

    $set = [];
    $set['online'] = 0;
    $set['latency'] = $ip_status['latency'];
    $set['last_check'] = $time_now;
    if ($ip_status['online']) {
        $set['online'] = 1;
        $set['last_seen'] = $time_now;
    }

    return $set;
}

/**
 *
 * @param string $ip
 * @param array<string, int> $timeout
 * @return array<string, float|string|int>
 */
function ping(string $ip, array $timeout = ['sec' => 0, 'usec' => 200000]): array
{
    /**
     * Track errors for graph
     */
    $ERROR_SOCKET_CREATE = -0.003;
    $ERROR_SOCKET_CONNECT = -0.002;
    $ERROR_TIMEOUT = -0.001;

    $status = [
        'online' => 0,
        'latency' => null,
    ];

    if (!is_int($timeout['sec']) || !is_int($timeout['usec'])) {
        $timeout = ['sec' => 0, 'usec' => 200000];
    }

    $tim_start = microtime(true);
    $protocolNumber = getprotobyname('icmp');
    $socket = socket_create(AF_INET, SOCK_RAW, $protocolNumber);

    if (!$socket) {
        $status['error'] = 'socket_create';
        $status['latency'] = $ERROR_SOCKET_CREATE;
        Log::notice("Pinging error socket creating: $ip");
        usleep(100000);
        return $status;
    }

    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);

    $type = "\x08"; // Echo Request
    $code = "\x00";
    $checksum = "\x00\x00"; // Placeholder for checksum
    $identifier = "\x00\x01"; // Identifier
    $sequence = "\x00\x01"; // Sequence number
    $payload = "ping";

    $package = $type . $code . $checksum . $identifier . $sequence . $payload;
    $checksum = calculateChecksum($package);
    $package = $type . $code . $checksum . $identifier . $sequence . $payload;

    if (!socket_sendto($socket, $package, strlen($package), 0, $ip, 0)) {
        $status['error'] = 'socket_sendto';
        $status['latency'] = $ERROR_SOCKET_CONNECT;
        Log::notice("Pinging error socket connect: $ip");
        socket_close($socket);
        usleep(100000);
        return $status;
    }

    $buffer = '';
    $from_ip = '';
    $port = 0;
    $response = socket_recvfrom($socket, $buffer, 255, 0, $from_ip, $port);
    if ($response !== false && $from_ip === $ip) {
        $icmp = substr($buffer, 20);
        $type = ord($icmp[0]);
        $code = ord($icmp[1]);

        // Type 8 is returned when host ping himself
        if (($type === 0 || $type === 8) && $code === 0 && verifyChecksum($icmp)) {
            $status['online'] = 1;
            $status['latency'] = round_latency(microtime(true) - $tim_start);
            socket_close($socket);
            return $status;
        } else {
            Log::notice("Response verify fail $type $code " . verifyChecksum($icmp));
        }
    }

    $status['error'] = 'timeout';
    $status['latency'] = $ERROR_TIMEOUT;
    socket_close($socket);
    usleep(100000);

    return $status;
}

/**
 *
 * @param string $ip
 * @return string|bool
 */
function get_mac(string $ip): string|bool
{
    $comm_path = check_command('arp');

    if (empty($comm_path)) {
        Log::warning('arp command not exists please install net-tools');
        return false;
    }
    $arp = $comm_path;

    $ip = trim($ip);

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }

    $result = run_cmd($arp, ['-a', $ip]);
    $explode_result = explode(' ', $result['stdout']);
    $result = trim($explode_result[3]);

    if (filter_var($result, FILTER_VALIDATE_MAC) === false) {
        return false;
    } else {
        return $result;
    }
}

/**
 * To ping()
 * @param string $data
 * @return string
 */
function calculateChecksum(string $data): string
{
    // Asegurar longitud par
    if (strlen($data) % 2 !== 0) {
        $data .= "\x00";
    }

    // Suma de palabras de 16 bits
    $sum = array_sum(unpack('n*', $data));

    // Ajustar carry bits
    $sum = ($sum >> 16) + ($sum & 0xFFFF);
    $sum += ($sum >> 16);

    // Invertir bits y empaquetar en formato binario
    return pack('n*', ~$sum);
}

/**
 * To ping()
 * @param string $icmp
 * @return bool
 */
function verifyChecksum(string $icmp): bool
{
    $sum = array_sum(unpack('n*', $icmp));
    $sum = ($sum >> 16) + ($sum & 0xFFFF);
    $sum += ($sum >> 16);
    return (~$sum & 0xFFFF) === 0;
}
