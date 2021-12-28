<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/* port_type = 2 (udp) only work for non DGRAM sockets, dgram need wait for response/ ping */

function ping_host_ports(array $host) {
    global $log;

    if (empty($host['ports']) || !valid_array($host['ports'])) {
        $log->warning("No check ports for host {$host['id']}:{$host['ip']}");
        return false;
    }
    $time_now = time();

    $err_code = $err_msg = '';
    $timeout = 1;
    //if local less tiemout
    (is_local_ip($host['ip'])) ? $timeout = 0.8 : null;

    //Custom timeout for host
    if (!empty($host['timeout'])) {
        $timeout = $host['timeout'];
    }

    $host_status = [];
    $host_status['online'] = 0;
    $host_status['warn_port'] = 0;
    $host_status['last_check'] = $time_now;

    foreach ($host['ports'] as $kport => $port) {
        $host_status['ports'][$kport] = $port;
        $host_status['ports'][$kport]['online'] = 0;

        $tim_start = microtime(true);
        $ip = $host['ip'];
        $port['port_type'] == 2 ? $ip = 'udp://' . $ip : null;
        $log->debug("Checking host ip:port {$host['ip']}:{$port['n']}");
        $conn = @fsockopen($ip, $port['n'], $err_code, $err_msg, $timeout);

        if (is_resource($conn)) {
            $host_status['online'] = 1;
            $host_status['last_seen'] = $time_now;
            $host_status['ports'][$kport]['online'] = 1;
            fclose($conn);
        } else {
            $host_status['warn_port'] = 1;
            //$host['ports'][$kport]['warn_port_msg'] = $port['port'] . ' port down';
            //$host['ports'][$kport]['err_code'] = $err_code;
            //$host['ports'][$kport]['err_msg'] = $err_msg;
        }
        $host_status['ports'][$kport]['latency'] = microtime(true) - $tim_start;
        //TODO port average?
        $host_status['latency'] = microtime(true) - $tim_start;
    }

    if ($host_status['online'] == 0) {
        $time_now = time();
        $host_ping = ping($host['ip'], ['sec' => 0, 'usec' => 100000]);
        if ($host_ping['isAlive']) {
            $host_status['online'] = 1;
        }
    }
    valid_array($host_status['ports']) ? $host_status['ports'] = json_encode($host_status['ports'], true) : null;

    return $host_status;
}

function ping_known_host(array $host) {

    $timeout = ['sec' => 0, 'usec' => 500000];
    $time_now = time();

    if (is_local_ip($host['ip'])) {
        $timeout = ['sec' => 0, 'usec' => 200000];
    }

    $ip_status = ping($host['ip'], $timeout);

    //echo "Pinging {$host['ip']}{$ip_status['isAlive']} ";
    $set = [];
    $set['online'] = 0;
    $set['warn_port'] = 0;
    $set['latency'] = $ip_status['latency'];
    $set['last_check'] = $time_now;
    if ($ip_status['isAlive']) {
        $set['online'] = 1;
        $set['last_seen'] = $time_now;
    }

    return $set;
}

function ping(string $ip, array $timeout = ['sec' => 1, 'usec' => 0]) {

    $tim_start = microtime(true);
    $status['isAlive'] = 0;

    if (count($timeout) < 2 || !isset($timeout['sec']) || !isset($timeout['usec'])) {
        $timeout = ['sec' => 0, 'usec' => 150000];
    }
    $protocolNumber = getprotobyname('icmp');
    $socket = socket_create(AF_INET, SOCK_RAW, $protocolNumber);
    if (!$socket) {
        $status['error'] = 'socket_create';
        $status['latency'] = microtime(true) - $tim_start;
        return $status;
    }

    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);
    if (!socket_connect($socket, $ip, 0)) {
        $status['error'] = 'socket_connect';
        $status['latency'] = microtime(true) - $tim_start;
        socket_close($socket);
        return $status;
    }

    $package = "\x08\x00\x19\x2f\x00\x00\x00\x00\x70\x69\x6e\x67";
    socket_send($socket, $package, strlen($package), 0);

    if (socket_read($socket, 255)) {
        $status['isAlive'] = 1;
        $status['latency'] = microtime(true) - $tim_start;
    } else {
        $status['error'] = 'timeout';
        $status['latency'] = microtime(true) - $tim_start;
    }

    socket_close($socket);

    return $status;
}

//Source https://stackoverflow.com/questions/15521725/php-generate-ips-list-from-ip-range/15613770

function build_iplist(string $net) {
    $parts = explode('/', $net);
    $exponent = 32 - $parts[1];
    $count = pow(2, $exponent);
    $start = ip2long($parts[0]) + 1;
    $end = ($start + $count) - 3;

    return array_map('long2ip', range($start, $end));
}

function get_hostname(string $ip) {
    return gethostbyaddr($ip);
}

function get_mac(string $ip) {
    global $log;

    $comm_path = check_command('arp');

    if (empty($comm_path)) {
        $log->warning('arp command not exists please install net-tools');
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

function send_magic_packet(int $host_id) {

}
