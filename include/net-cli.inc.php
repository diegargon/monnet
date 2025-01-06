<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
/**
 *
 * @param AppContext $ctx
 * @return bool
 */
function check_known_hosts(AppContext $ctx): bool
{
    $lng = $ctx->get('lng');
    $db = $ctx->get('Mysql');
    $hosts = $ctx->get('Hosts');

    if (!is_object($hosts)) {
        Log::err("hosts is not a object");
        return false;
    }
    Log::debug("Pinging known host");

    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) {
        $new_host_status = [];
        /* Port Scan */
        if ($host['check_method'] == 2) { //Ports
            Log::debug("Pinging host ports {$host['ip']}");
            $check_ports_result = check_host_ports($ctx, $host);

            // If host change status to off  we check again
            if ($host['online'] == 1 && $check_ports_result['online'] == 0) :
                $check_ports_result = check_host_ports($ctx, $host);
            endif;

            $new_host_status = [
                'online' => $check_ports_result['online'],
                'warn' =>  $check_ports_result['warn'],
                'latency' => $check_ports_result['latency']
            ];
            $ports_status = $check_ports_result['ports'];
            foreach ($ports_status as $portid => $new_port_status) :
                $db->update('ports', $new_port_status, ['id' => $portid]);
            endforeach;
        } else { /* Ping Scan */
            if (!empty($host['disable_ping'])) :
                continue;
            endif;

            if ($host['check_method'] == 2 && !valid_array($host['ports'])) :
                Log::warning("No check ports for host {$host['id']}:{$host['display_name']}, pinging.");
            endif;
            $ping_host_result = ping_known_host($ctx, $host);
            //recheck if was online
            if ($host['online'] == 1 && $ping_host_result['online'] == 0) :
                $ping_host_result = ping_known_host($ctx, $host);
            endif;

            (valid_array($ping_host_result)) ? $new_host_status = $ping_host_result : null;
        }

        /*  Update host with scan data */

        if (valid_array($new_host_status)) {
            if ($new_host_status['online'] && empty($host['mac'])) {
                $mac = get_mac($host['ip']);
                $new_host_status['mac'] = !empty($mac) ? $mac : null;
            }
            if ($host['online'] == 0 && $new_host_status['online'] == 1) {
                $new_host_status['online_change'] = date_now();
                $log_msg = $host['display_name'] . ': ' . $lng['L_HOST_BECOME_ON'];
                Log::logHost('LOG_NOTICE', $host['id'], $log_msg);
                if (!empty($host['alarm_port_email'])) :
                    $hosts->sendHostMail($host['id'], $log_msg);
                endif;

                //Try get hostname when a host become on
                $hostname = $hosts->getHostname($host['ip']);
                if ($hostname && $hostname !== $host['hostname'] && $hostname !== $host['ip']) :
                    $new_host_status['hostname'] = $hostname;
                endif;
            } elseif ($host['online'] == 1 && $new_host_status['online'] == 0) {
                $new_host_status['online_change'] = date_now();
                //$host_timeout = !empty($host['timeout']) ? '(' . $host['timeout'] . ')' : '';
                $log_msg = $host['display_name'] . ': ' . $lng['L_HOST_BECOME_OFF'];
                Log::logHost('LOG_WARNING', $host['id'], $log_msg, LT_ALERT);
                if (!empty($host['alarm_ping_email'])) :
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
    Log::debug('Finish check_known_hosts');

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
    $timeout = ['sec' => 0, 'usec' => 100000];

    $db_hosts = $hosts->getAll();
    $iplist = $networks->buildIpScanList();

    //We remove known hosts since we checked in other functions
    foreach ($iplist as $kip => $vip) :
        $iplist[$kip] = trim($vip);
        foreach ($db_hosts as $host) :
            if ($host['ip'] == $vip) :
                unset($iplist[$kip]);
            endif;
        endforeach;
    endforeach;

    foreach ($iplist as $ip) :
        $latency = microtime(true);

        $ip_status = ping($ip, $timeout);
        $set = [];

        if ($ip_status['online']) :
            //New host
            $mac = get_mac($ip);
            if ($mac) :
                $set['mac'] = trim($mac);
                $mac_info = get_mac_vendor($mac);
                $set['mac_vendor'] = (!empty($mac_info['company'])) ? $mac_info['company'] : '-';
            endif;
            $set['ip'] = $ip;
            $set['online'] = 1;
            $set['warn'] = 1;

            $network_id = $networks->getNetworkIDbyIP($ip);
            if ($network_id == false) {
                Log::warning('Failed to get id network for ip ' . $ip);
                //TODO 1???
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
    endforeach;

    Log::info('Ping net took ' . (intval(microtime(true) - $ping_net_time)) . ' seconds');
}

/**
 *
 * @param Hosts $hosts
 * @param int $forceall
 */
function fill_hostnames(Hosts $hosts, int $forceall = 0): void
{
    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) {
        if (empty($host['hostname']) || $forceall === 1) {
            //Log::debug("Getting hostname {$host['ip']}");
            $hostname = $hosts->getHostname($host['ip']);
            if ($hostname !== false && $hostname != $host['ip']) {
                $update['hostname'] = $hostname;
                $hosts->update($host['id'], $update);
            }
        }
    }
}

/**
 *
 * @param Hosts $hosts
 * @param int $forceall
 */
function fill_mac_vendors(Hosts $hosts, int $forceall = 0): void
{
    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) {
        $vendor = [];
        $update = [];

        if (
            (!empty($host['mac'])) &&
            (empty($host['mac_vendor']) || $forceall === 1)
        ) {
            Log::debug("Getting mac vendor for {$host['display_name']}");
            $vendor = get_mac_vendor_local(trim($host['mac']));
            if (empty($vendor)) :
                Log::debug("Local lookup fail, checking mac online");
                $vendor = get_mac_vendor(trim($host['mac']));
            endif;

            if (empty($vendor['company'])) :
                Log::debug("Mac vendor for {$host['mac']} is null");
                (empty($host['mac_vendor'])) ? $update['mac_vendor'] = '-' : null;
            else :
                if (!empty($host['mac_vendor']) && ($vendor['company'] != $host['mac_vendor'])) :
                    Log::warning("Mac vendor change from {$host['mac_vendor']} to {$vendor['company']} ] updating...");
                endif;
                $update['mac_vendor'] = $vendor['company'];
            endif;
            if (!empty($update) && ($host['mac_vendor'] != $update['mac_vendor'])) :
                $hosts->update($host['id'], $update);
            endif;
        }
    }
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
    foreach ($known_hosts as $host) {
        $new_mac = get_mac($host['ip']);
        if (!empty($new_mac) && $host['mac'] != $new_mac) :
            $update['mac'] = trim($new_mac);
            $hosts->update($host['id'], $update);
        endif;
    }
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
    $log_type = 2; // ports related
    $latency = [];

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


    if (!empty($host['timeout'])) :
        $timeout = $host['timeout'];
    else :
        $timeout = $networks->isLocal($host['ip']) ? 0.6 : 1;
    endif;

    foreach ($ports as $port) :
        $error_code = $error_msg = '';
        $port_status = [];
        $port_status['last_change'] = date_now();

        $tim_start = microtime(true);
        $ip = trim($host['ip']);
        $port['protocol'] == 2 ? $ip = 'udp://' . $ip : null;

        $conn = @fsockopen($ip, $port['pnumber'], $error_code, $error_msg, $timeout);

        if (is_resource($conn)) :
            $host_result['online'] = 1; // Host is online
            $port_status['online'] = 1;
            $latency[] = round_latency(microtime(true) - $tim_start);
            if ((int) $port['online'] === 0) :
                Log::logHost('LOG_NOTICE', $host['id'], 'Port become Online');
            endif;
            fclose($conn);
        elseif (empty($host['alarm_port_disable'])) :
            $log_msg = "Port {$port['pnumber']} down: $error_msg ($error_code)";
            Log::logHost('LOG_WARNING', $host['id'], $log_msg, $log_type);
            $host_result['warn'] = 1;
        endif;

        $host_result['ports'][$port['id']] = $port_status;
    endforeach;

    if ($host_result['online'] === 0) :
        /*
         * Todos los puertos caidos probamos si el host esta online
         * con ping
         */
        if (!empty($host['timeout']) && $host['timeout'] > 0.0) {
            $sec = intval($host['timeout']);
            $usec = ($host['timeout'] - $sec);
            $usec = $usec > 0 ? $usec * 1000000 : 0;
        } else {
            $sec = 0;
            $usec = 100000;
        }
        $host_ping = ping($host['ip'], ['sec' => $sec, 'usec' => $usec]);
        if ($host_ping['online']) :
            $host_result['online'] = 1;
            $host_result['latency'] = $host_ping['latency'];
        else :
            Log::logHost('LOG_WARNING', $host['id'], 'All Ports down and no ping response from the host');
        endif;
    else :
        // Calculamos la media latencia puertos
        if (count($latency) > 0) :
            $average_latency = array_sum($latency) / count($latency);
            $host_result['latency'] = $average_latency;
        endif;
    endif;

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
    $sec = 0;
    $usec = 500000;
    $time_now = date_now();
    $networks = $ctx->get('Networks');

    if (!empty($host['timeout']) && $host['timeout'] > 0.0) {
        $sec = intval($host['timeout']);
        $usec = ($host['timeout'] - $sec);
        $usec = $usec > 0 ? $usec * 1000000 : 0;
    } elseif ($networks->isLocal($host['ip'])) {
        $sec = 0;
        $usec = ($host['online']) ? 400000 : 300000;
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
function ping(string $ip, array $timeout = ['sec' => 1, 'usec' => 0]): array
{
    /**
     * Track errors for graph
     */
    $ERROR_SOCKET_CREATE = -0.003;
    $ERROR_SOCKET_CONNECT = -0.002;
    $ERROR_TIMEOUT = -0.001;

    $retries = 2;
    $attempts = 0;

    $status = [
        'online' => 0,
        'latency' => null,
    ];


    if (!isset($timeout['sec'], $timeout['usec']) || !is_int($timeout['sec']) || !is_int($timeout['usec'])) {
        $timeout = ['sec' => 0, 'usec' => 200000];
    }
    $protocolNumber = getprotobyname('icmp');

    while ($attempts < $retries) {
        $attempts++;
        $tim_start = microtime(true);

        $socket = socket_create(AF_INET, SOCK_RAW, $protocolNumber);
        if (!$socket) {
            $status['error'] = 'socket_create';
            $status['latency'] = $ERROR_SOCKET_CREATE;
            socket_close($socket);
            usleep(200000);
            continue;
        }

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);

        if (!socket_connect($socket, $ip, 0)) {
            $status['error'] = 'socket_connect';
            $status['latency'] = $ERROR_SOCKET_CONNECT;
            socket_close($socket);
            usleep(200000);
            continue;
        }

        $type = "\x08"; // Echo Request
        $code = "\x00";
        $checksum = "\x00\x00"; // Placeholder for checksum
        $identifier = "\x00\x01"; // Identifier
        $sequence = "\x00\x01"; // Sequence number
        $payload = "ping";

        //$package = "\x08\x00\x19\x2f\x00\x00\x00\x00\x70\x69\x6e\x67";
        $package = $type . $code . $checksum . $identifier . $sequence . $payload;
        $checksum = calculateChecksum($package);
        $package = $type . $code . $checksum . $identifier . $sequence . $payload;

        socket_send($socket, $package, strlen($package), 0);

        $buffer = '';
        $from_ip = '';
        $port = 0;
        $response = socket_recvfrom($socket, $buffer, 255, 0, $from_ip, $port);
        if ($response !== false && $from_ip === $ip) {
            $icmp = substr($buffer, 20);
            $type = ord($icmp[0]);
            $code = ord($icmp[1]);

            if ($type === 0 && $code === 0 && verifyChecksum($icmp)) {
                $status['online'] = 1;
                $status['latency'] = round_latency(microtime(true) - $tim_start);
                socket_close($socket);
                return $status;
            }
        }

        $status['error'] = 'timeout';
        $status['latency'] = $ERROR_TIMEOUT;
        socket_close($socket);
        usleep(200000);
    }

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
    $sum = array_sum(unpack('n*', $data));
    $sum = ($sum >> 16) + ($sum & 0xFFFF);
    $sum += ($sum >> 16);
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
