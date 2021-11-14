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
    if (empty($host['ports']) || !valid_array($host['ports'])) {
        //Log err/warning no ports
        //var_dump($host);
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

    $host['host']['warn_port'] = 0;
    foreach ($host['ports'] as $kport => $port) {
        $tim_start = microtime(true);
        $ip = $host['ip'];
        $port['port_type'] == 2 ? $ip = 'udp://' . $ip : null;

        $conn = @fsockopen($ip, $port['port'], $err_code, $err_msg, $timeout);
        if (is_resource($conn)) {
            $host['online'] = 1;
            $host['last_seen'] = $time_now;
            $host['ports'][$kport]['online'] = 1;
            fclose($conn);
        } else {
            $host['ports'][$kport]['online'] = 0;
            $host['warn_port'] = 1;
            $host['ports'][$kport]['warn_port_msg'] = $port['port'] . ' port down';
            $host['ports'][$kport]['err_code'] = $err_code;
            $host['ports'][$kport]['err_msg'] = $err_msg;
        }
        $host['ports'][$kport]['latency'] = microtime(true) - $tim_start;
        //TODO port average
        $host['latency'] = microtime(true) - $tim_start;
    }

    return $host;
}

function ping_all_ports(array &$hosts) {

    foreach ($hosts as $khost => $host) {
        $err_code = $err_msg = '';
        $timeout = 1;
        $hosts[$khost]['online'] = 0;

        //if local less tiemout
        (is_local_ip($host['ip'])) ? $timeout = 0.8 : null;

        //Custom timeout for host
        if (!empty($host['timeout'])) {
            $timeout = $host['timeout'];
        }

        if (!empty($host['ports']) && count($host['ports']) > 0) {
            foreach ($host['ports'] as $kport => $value_port) {
                $tim_start = microtime(true);
                $ip = $host['ip'];
                $value_port['port_type'] == 2 ? $ip = 'udp://' . $ip : null;

                $conn = @fsockopen($ip, $value_port['port'], $err_code, $err_msg, $timeout);
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
                $hosts[$khost]['ports'][$kport]['latency'] = microtime(true) - $tim_start;
            }
        }
    }
}

function ping(string $ip, array $timeout = []) {

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

function get_mac(string $ip) {
    //TODO better
    $ip = trim($ip);

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    $arp = "arp -a $ip | awk '{print $4}'";
    $result = shell_exec($arp);

    if (filter_var(trim($result), FILTER_VALIDATE_MAC) === false) {
        return false;
    } else {
        return $result;
    }
}

function is_local_ip($ip) {
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return true;
    }
    return false;
}

function send_magic_packet($host_id) {

}
