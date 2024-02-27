<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/* port_type = 2 (udp) only work for non DGRAM sockets, dgram need wait for response/ ping */

function ping_host_ports(array $host) {
    $time_now = utc_date_now();

    $err_code = $err_msg = '';
    $timeout = is_local_ip($host['ip']) ? 0.6 : 1;

    //Custom timeout for host
    if (!empty($host['timeout'])) {
        $timeout = $host['timeout'];
    }

    $host_status = [];
    $host_status['online'] = 0;
    $host_status['warn_port'] = 0;
    $host_status['warn_msg'] = '';
    $host_status['last_check'] = $time_now;

    foreach ($host['ports'] as $kport => $port) {
        $host_status['ports'][$kport] = $port;
        $host_status['ports'][$kport]['online'] = 0;

        $tim_start = microtime(true);
        $ip = $host['ip'];
        $port['port_type'] == 2 ? $ip = 'udp://' . $ip : null;
        $conn = @fsockopen($ip, $port['n'], $err_code, $err_msg, $timeout);

        if (is_resource($conn)) {
            $host_status['online'] = 1;
            $host_status['last_seen'] = $time_now;
            $host_status['ports'][$kport]['online'] = 1;
            fclose($conn);
            $latency = round_latency(microtime(true) - $tim_start);
            $host_status['ports'][$kport]['latency'] = $latency;
            $host_status['latency'] = $latency;
        } else {
            $warn_msg = 'Port ' . $port['n'] . ' down' . "\n";
            $host_status['warn_port'] = 1;
            $host_status['warn_msg'] .= $warn_msg;
            $host['ports'][$kport]['warn_port_msg'] = $warn_msg;
            $host['ports'][$kport]['err_code'] = $err_code;
            $host['ports'][$kport]['err_msg'] = $err_msg;
        }
    }

    if ($host_status['online'] == 0) {
        $host_ping = ping($host['ip'], ['sec' => 0, 'usec' => 100000]);
        if ($host_ping['online']) {
            $host_status['online'] = 1;
        }
    }
    valid_array($host_status['ports']) ? $host_status['ports'] = json_encode($host_status['ports'], true) : null;

    return $host_status;
}

function ping_known_host(array $host) {
    $usec = 500000;
    $time_now = utc_date_now();

    if (is_local_ip($host['ip'])) {
        $usec = ($host['online']) ? 400000 : 300000;
    }

    $timeout = ['sec' => 0, 'usec' => $usec];

    $ip_status = ping($host['ip'], $timeout);

    $set = [];
    $set['online'] = 0;
    $set['warn_port'] = 0;
    $set['latency'] = $ip_status['latency'];
    $set['last_check'] = $time_now;
    if ($ip_status['online']) {
        $set['online'] = 1;
        $set['last_seen'] = $time_now;
    }

    return $set;
}

function ping(string $ip, array $timeout = ['sec' => 1, 'usec' => 0]) {

    $status = [
        'online' => 0,
        'latency' => -0.003,
    ];

    $tim_start = microtime(true);

    if (count($timeout) < 2 || !isset($timeout['sec']) || !isset($timeout['usec'])) {
        $timeout = ['sec' => 0, 'usec' => 200000];
    }
    $protocolNumber = getprotobyname('icmp');
    $socket = socket_create(AF_INET, SOCK_RAW, $protocolNumber);
    if (!$socket) {
        $status['error'] = 'socket_create';
        $status['latency'] = -0.003;
    }

    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);
    if (!socket_connect($socket, $ip, 0)) {
        $status['error'] = 'socket_connect';
        $status['latency'] = -0.002;
        socket_close($socket);
        return $status;
    }

    $package = "\x08\x00\x19\x2f\x00\x00\x00\x00\x70\x69\x6e\x67";
    socket_send($socket, $package, strlen($package), 0);

    if (socket_read($socket, 255)) {
        $status['online'] = 1;
        $status['latency'] = round_latency(microtime(true) - $tim_start);
    } else {
        $status['error'] = 'timeout';
        $status['latency'] = -0.001;
    }

    socket_close($socket);

    return $status;
}

function get_hostname(string $ip) {
    return gethostbyaddr($ip);
}

function get_mac(string $ip) {

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

function is_local_ip(string $ip) {
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return true;
    }
    return false;
}

function sendWOL(string $host_mac) {

    Log::debug("checking mac \"{$host_mac}\"");
    $host_mac = str_replace([':', '-'], '', $host_mac);

    if (strlen($host_mac) % 2 !== 0) {
        Log::err("MAC address must be even \"{$host_mac}\"");
        return false;
    }

    $macAddressBinary = hex2bin($host_mac);
    $magicPacket = str_repeat(chr(255), 6) . str_repeat($macAddressBinary, 16);
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($socket === false) {
        Log::err("Error creating socket " . socket_strerror(socket_last_error()));
        return false;
    }

    socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
    $result = socket_sendto($socket, $magicPacket, strlen($magicPacket), 0, '255.255.255.255', 9);

    if ($result) {
        Log::debug("Successful sending WOL packet to {$host_mac}");
    } else {
        Log::debug("Failed sending WOL packet to {$host_mac}");
    }
    // Cerrar el socket
    socket_close($socket);

    return $result ? true : false;
}

function validatePortsInput(string $input) {
    // Split the input by commas
    $values = explode(',', $input);
    $valid_values = [];

    foreach ($values as $value) {
        // Split the value by /
        $parts = explode('/', $value);

        // Check if there are three parts
        if (count($parts) == 3) {
            // Check if the first part is a number between 1 and 65535
            if (is_numeric($parts[0]) && $parts[0] >= 1 && $parts[0] <= 65535) {
                // Check if the second part is either 'tcp' or 'udp'
                if ($parts[1] === "tcp" || $parts[1] === "udp") {
                    $port_type = $parts[1] === "tcp" ? 1 : 2;
                    $name = $parts[2]; // Get the port name
                    // Add the valid port information to the array
                    $valid_values[] = [
                        'n' => $parts[0],
                        'name' => $name,
                        'port_type' => $port_type,
                        'online' => 0,
                        'latency' => 0.0
                    ];
                }
            }
        }
    }

    return $valid_values; // Return the array of valid values
}
