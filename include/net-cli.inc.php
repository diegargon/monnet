<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function check_known_hosts(AppCtx $ctx)
{
    $lng = $ctx->getAppLang();
    $db = $ctx->getAppDb();
    $hosts = $ctx->getAppHosts();

    if (!is_object($hosts)) {
        Log::err("hosts is not a object");
        return false;
    }
    Log::debug("Pinging known host");

    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host)
    {
        $new_host_status = [];
        /*
         * Port Scan
         */
        if ($host['check_method'] == 2 && valid_array($host['ports'])) { //TCP
            Log::debug("Pinging host ports {$host['ip']}");
            $ping_ports_result = ping_host_ports($ctx, $host);
            if ($host['online'] == 1 && $ping_ports_result['online'] == 0) { //recheck
                $ping_ports_result = ping_host_ports($ctx, $host);
            }
            //Ports are down, check host with ping
            if ($ping_ports_result['online'] == 0) {
                $host_ping_result = ping($host['ip'], ['sec' => 0, 'usec' => 100000]);
                if ($host_ping_result['online']) {
                    $ping_ports_result['online'] = 1;
                    $ping_ports_result['latency'] = $host_ping_result['latency'];
                    $ping_ports_result['last_seen'] = utc_date_now();
                } else {
                    $ping_ports_result['online'] = 0;
                }
            }
            (valid_array($ping_ports_result)) ? $new_host_status = $ping_ports_result : null;

            /*
             * Ping Scan
             */
        } else {
            if ($host['check_method'] == 2 && !valid_array($host['ports'])) {
                Log::warning("No check ports for host {$host['id']}:{$host['display_name']}, pinging.");
            }
            $ping_host_result = ping_known_host($ctx, $host);
            if ($host['online'] == 1 && $ping_host_result['online'] == 0) {
                //recheck
                $ping_host_result = ping_known_host($ctx, $host);
            }

            (valid_array($ping_host_result)) ? $new_host_status = $ping_host_result : null;
        }

        /*
         *  Update host with scan data
         */

        if (valid_array($new_host_status) && $new_host_status['online'] && empty($host['mac'])) {
            $mac = get_mac($host['ip']);
            $new_host_status['mac'] = !empty($mac) ? $mac : null;
        }
        if (valid_array($new_host_status)) {

            if ($host['online'] == 0 && $new_host_status['online'] == 1) {
                $new_host_status['online_change'] = utc_date_now();
                Log::logHost('LOG_NOTICE', $host['id'], $host['display_name'] . ': ' . $lng['L_HOST_BECOME_ON']);
            } elseif ($host['online'] == 1 && $new_host_status['online'] == 0) {
                $new_host_status['online_change'] = utc_date_now();
                $host_timeout = !empty($host['timeout']) ? '(' . $host['timeout'] . ')' : '';
                Log::logHost('LOG_NOTICE', $host['id'], $host['display_name'] . ': ' . $lng['L_HOST_BECOME_OFF'] . $host_timeout);
            }

            $hosts->update($host['id'], $new_host_status);
            if ($new_host_status['online'] == 1 && isset($new_host_status['latency'])) {
                $ping_latency = $new_host_status['latency'];
                $set_ping_stats = ['date' => utc_date_now(), 'type' => 1, 'host_id' => $host['id'], 'value' => $ping_latency];
                $db->insert('stats', $set_ping_stats);
            }
        } else {
            Log::warning("Known host ping status error {$host['id']}:{$host['display_name']}");
        }
    }
    Log::debug('Finish check_known_hosts');
}

function ping_net(AppCtx $ctx)
{
    $hosts = $ctx->getAppHosts();
    $networks = $ctx->getAppNetworks();
    $ping_net_time = microtime(true);
    $timeout = ['sec' => 0, 'usec' => 100000];

    $db_hosts = $hosts->getAll();
    $iplist = $networks->buildIpList();

    //We remove known hosts since we checked in other functions
    foreach ($iplist as $kip => $vip)
    {
        $vip = trim($vip);
        $iplist[$kip] = $vip;
        foreach ($db_hosts as $host)
        {
            if ($host['ip'] == $vip) {
                unset($iplist[$kip]);
            }
        }
    }

    foreach ($iplist as $ip)
    {
        $latency = microtime(true);

        $ip_status = ping($ip, $timeout);
        $set = [];

        if ($ip_status['online']) {
            $mac = trim(get_mac($ip));
            if ($mac) {
                $set['mac'] = $mac;
                $mac_info = get_mac_vendor($mac);
                $set['mac_vendor'] = (!empty($mac_info['company'])) ? $mac_info['company'] : '-';
            }
            $set['ip'] = $ip;
            $set['online'] = 1;

            $network_id = $networks->getNetworkIDbyIP($ip);
            if ($network_id == false) {
                Log::warning('Failed to get id network for ip ' . $ip);
                $set['network'] = 1;
            } else {
                $set['network'] = $network_id;
            }

            $set['latency'] = round_latency($latency);
            $set['last_seen'] = utc_date_now();
            $hostname = $hosts->getHostname($ip);
            !empty($hostname) && ($hostname != $ip) ? $set['hostname'] = $hostname : null;

            $hosts->insert($set);
        }
    }

    Log::info('Ping net took ' . (intval(microtime(true) - $ping_net_time)) . ' seconds');
}

function fill_hostnames(Hosts $hosts, int $forceall = 0)
{
    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host)
    {
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

function fill_mac_vendors(Hosts $hosts, int $forceall = 0)
{
    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host)
    {
        $vendor = [];
        $update = [];

        if ((!empty($host['mac'])) &&
                (empty($host['mac_vendor']) || $forceall === 1)
        ) {
            Log::debug("Getting mac vendor for {$host['display_name']}");
            $vendor = get_mac_vendor_local(trim($host['mac']));
            if (empty($vendor)) {
                Log::debug("Local lookup fail, checking mac online");
                $vendor = get_mac_vendor(trim($host['mac']));
            }

            if (empty($vendor['company'])) {
                Log::debug("Mac vendor for {$host['mac']} is null");
                (empty($host['mac_vendor'])) ? $update['mac_vendor'] = '-' : null;
            } else {
                if ($vendor['company'] != $host['mac_vendor']) {
                    Log::warning("Mac vendor change from {$host['mac_vendor']} to {$vendor['company']} ] updating...");
                    $update['mac_vendor'] = $vendor['company'];
                }
            }
            if (!empty($update) && ($host['mac_vendor'] != $update['mac_vendor'])) {
                $hosts->update($host['id'], $update);
            }
        }
    }
}

function check_macs(Hosts $hosts)
{

    $known_hosts = $hosts->getknownEnabled();

    Log::info('Checking macs');
    foreach ($known_hosts as $host)
    {
        $new_mac = get_mac($host['ip']);
        if (!empty($new_mac) && $host['mac'] != $new_mac) {
            $update['mac'] = trim($new_mac);
            $hosts->update($host['id'], $update);
        }
    }
}

function host_access(array $cfg, Hosts $hosts)
{

    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host)
    {
        if ($host['access_method'] < 1 || empty($host['online'])) {
            continue;
        }

        $ssh_conn_result = [];
        $set = [];
        $set['access_results'] = [];

        $ssh = ssh_connect_host($cfg, $ssh_conn_result, $host);
        if (!$ssh) {
            Log::err("SSH host_access: Cant connect host {$host['ip']}");
            continue;
        }
        Log::info("SSH hosts access: Succesful connect to {$host['ip']}");
        $ssh->setKeepAlive(1);

        $results = [];

        if (empty($host['hostname'])) {
            h_get_hostname($ssh, $results);
        }
        h_get_ncpus($ssh, $results);
        h_get_sys_mem($ssh, $results);
        h_get_sys_space($ssh, $results);
        h_get_uptime($ssh, $results);
        h_get_load_average($ssh, $results);
        h_get_tail_syslog($ssh, $results);

        if (!empty($results['hostname'])) {
            $set['hostname'] = $results['hostname'];
            unset($results['hostname']);
        }
        unset($results['motd']);
        $set['access_results'] = json_encode($results);

        $hosts->update($host['id'], $set);
    }
}

/* port_type = 2 (udp) only work for non DGRAM sockets, dgram need wait for response/ ping */

function ping_host_ports(AppCtx $ctx, array $host)
{
    $time_now = utc_date_now();

    $networks = $ctx->getAppNetworks();

    $err_code = $err_msg = '';

    //Custom timeout for host
    if (!empty($host['timeout'])) {
        $timeout = $host['timeout'];
    } else {
        $timeout = $networks->isLocal($host['ip']) ? 0.6 : 1;
    }

    $host_status = [];
    $host_status['online'] = 0;
    $host_status['warn_port'] = 0;
    $host_status['warn_msg'] = '';
    $host_status['last_check'] = $time_now;

    foreach ($host['ports'] as $kport => $port)
    {
        $host_status['ports'][$kport] = $port;
        $host_status['ports'][$kport]['online'] = 0;
        $host_status['ports'][$kport]['user'] = !empty($port['user'] ? 1 : 0);

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
        if (!empty($host['timeout']) && $host['timeout'] > 0.0) {
            $sec = intval($host['timeout']);
            $usec = ($host['timeout'] - $sec);
            $usec = $usec > 0 ? $usec * 1000000 : 0;
        } else {
            $sec = 0;
            $usec = 100000;
        }

        $host_ping = ping($host['ip'], ['sec' => $sec, 'usec' => $usec]);
        if ($host_ping['online']) {
            $host_status['online'] = 1;
        }
    }
    valid_array($host_status['ports']) ? $host_status['ports'] = json_encode($host_status['ports'], true) : null;

    return $host_status;
}

function ping_known_host(AppCtx $ctx, array $host)
{
    $sec = 0;
    $usec = 500000;
    $time_now = utc_date_now();
    $networks = $ctx->getAppNetworks();

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
    $set['warn_port'] = 0;
    $set['latency'] = $ip_status['latency'];
    $set['last_check'] = $time_now;
    if ($ip_status['online']) {
        $set['online'] = 1;
        $set['last_seen'] = $time_now;
    }

    return $set;
}

function ping(string $ip, array $timeout = ['sec' => 1, 'usec' => 0])
{

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

function get_mac(string $ip)
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
