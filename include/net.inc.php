<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/* TODO: Only TCP port check (check_type=1), add ping check at least (check_type=2) */
/* port_type = 2 (udp) only work for non DGRAM sockets, dgram need wait for response/ ping */

function ping_ports(array &$hosts) {

    foreach ($hosts as $khost => $host) {
        $err_code = $err_msg = '';
        $timeout = 1;
        $hosts[$khost]['online'] = 0;

        if (!empty($host['timeout'])) {
            $timeout = $host['timeout'];
        }

        if (!empty($host['ports']) && count($host['ports']) > 0) {
            foreach ($host['ports'] as $kport => $value_port) {
                $tim_start = microtime(true);
                $hostname = $host['ip'];
                $value_port['port_type'] == 2 ? $hostname = 'udp://' . $hostname : null;

                $conn = @fsockopen($hostname, $value_port['port'], $err_code, $err_msg, $timeout);
                //echo "Conn " . $hostname . ' port ' . $value_port['port'] . "";
                if (is_resource($conn)) {
                    //echo " ok \n";
                    $hosts[$khost]['online'] = 1;
                    $hosts[$khost]['ports'][$kport]['online'] = 1;
                    fclose($conn);
                } else {
                    $hosts[$khost]['ports'][$kport]['online'] = 0;
                    $hosts[$khost]['ports'][$kport]['err_code'] = $err_code;
                    $hosts[$khost]['ports'][$kport]['err_msg'] = $err_msg;
                    //echo " fail \n";
                }
                $hosts[$khost]['ports'][$kport]['latency'] = round(microtime(true) - $tim_start, 2);
            }
        }
    }
}

function ping(string $ip, array $timeout = []) {

    $tim_start = microtime(true);
    $status['isAlive'] = 0;

    if (count($timeout) < 1) {
        $timeout = ['sec' => 0, 'usec' => 100000];
    }
    $protocolNumber = getprotobyname('icmp');
    $socket = socket_create(AF_INET, SOCK_RAW, $protocolNumber);
    if (!$socket) {
        $status['error'] = 'socket_create';
        $status['latency'] = round(microtime(true) - $tim_start, 2);
        return $status;
    }

    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);
    if (!socket_connect($socket, $ip, 0)) {
        $status['error'] = 'socket_connect';
        $status['latency'] = round(microtime(true) - $tim_start, 2);
        socket_close($socket);
        return $status;
    }

    $package = "\x08\x00\x19\x2f\x00\x00\x00\x00\x70\x69\x6e\x67";
    socket_send($socket, $package, strlen($package), 0);

    if (socket_read($socket, 255)) {
        $status['isAlive'] = 1;
        $status['latency'] = round(microtime(true) - $tim_start, 2);
    } else {
        $status['error'] = 'timeout';
        $status['latency'] = round(microtime(true) - $tim_start, 2);
    }

    socket_close($socket);

    return $status;
}

//Source https://stackoverflow.com/questions/15521725/php-generate-ips-list-from-ip-range/15613770

function get_iplist(string $net) {
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

function send_magic_packet($host_id) {
    
}
