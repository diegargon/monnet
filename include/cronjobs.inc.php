<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function check_known_hosts(Database $db, Hosts $hosts) {
    global $log;

    if (!is_object($hosts)) {
        $log->err("hosts is not a object");
        return false;
    }

    $db_hosts = $hosts->getEnabled();

    foreach ($db_hosts as $host) {
        $host_status = [];

        if ($host['check_method'] == 2) { //TCP
            $log->info("Pinging host ports {$host['ip']}");
            $host_status = ping_host_ports($host);
        } else { //Ping
            $log->info("Pinging host {$host['ip']}");
            $host_status = ping_known_host($host);
        }
        if (empty($host['mac'])) {
            $mac = get_mac($host['ip']);
            $mac ? $host_status['mac'] = $mac : null;
        }
        if (valid_array($host_status)) {
            $log->info("Update known host {$host['id']}:{$host['ip']}");
            defined('DUMP_VARS') ? $log->debug("Dumping host_status: " . print_r($host_status, true)) : null;
            $hosts->update($host['id'], $host_status);
            $ping_latency = $host_status['latency'];
            $set_ping_stats = ['date' => get_datetime_now('UTC'), 'type' => 1, 'host_id' => $host['id'], 'value' => $ping_latency];
            $db->insert('stats', $set_ping_stats);
        }
    }
}

function ping_net(array $cfg, Hosts $hosts) {
    global $log;

    $timeout = ['sec' => 0, 'usec' => 110000];

    $log->info('Pinging NET ' . $cfg['net']);

    $db_hosts = $hosts->getAll();

    $iplist = build_iplist($cfg['net']);

    /*
     * Remove known hosts since we checked in other functions
     */

    foreach ($iplist as $kip => $vip) {
        $vip = trim($vip);
        $iplist[$kip] = $vip;
        foreach ($db_hosts as $host) {
            if ($host['ip'] == $vip) {
                unset($iplist[$kip]);
            }
        }
    }

    foreach ($iplist as $ip) {
        $latency = microtime(true);

        $ip_status = ping($ip, $timeout);
        $set = [];

        if ($ip_status['isAlive']) {
            $mac = trim(get_mac($ip));
            $mac_vendor = '';
            if ($mac) {
                $set['mac'] = $mac;
                $mac_info = get_mac_vendor($mac);
                (!empty($mac_info['company'])) ? $set['mac_vendor'] = $mac_info['company'] : $set['mac_vendor'] = '-';
            }
            $log->info("Discover host $ip:$mac:$mac_vendor");

            $set['ip'] = $ip;
            $set['online'] = 1;
            $set['latency'] = microtime(true) - $latency;
            $set['last_seen'] = time();
            $hostname = get_hostname($ip);
            !empty($hostname) && ($hostname != $ip) ? $set['hostname'] = $hostname : null;

            $hosts->insert($set);
        }
    }
}

function fill_hostnames(Hosts $hosts, int $only_missing = 0) {
    global $log;

    $db_hosts = $hosts->getEnabled();

    foreach ($db_hosts as $host) {
        if (empty($host['hostname']) || $only_missing === 0) {
            $log->debug("Getting hostname {$host['ip']}");
            $hostname = get_hostname($host['ip']);
            if ($hostname !== false && $hostname != $host['ip']) {
                $log->debug("Updating hostname {$host['ip']}: $hostname");
                $update['hostname'] = $hostname;
                $hosts->update($host['id'], $update);
            }
        }
    }
}

function fill_mac_vendors(Hosts $hosts, int $only_missing = 0) {
    global $log;

    $db_hosts = $hosts->getEnabled();

    foreach ($db_hosts as $host) {
        if (!empty($host['mac']) && (empty($host['mac_vendor']) || $only_missing === 0)) {
            $log->debug("Getting mac vendor for {$host['mac']}");
            $vendor = get_mac_vendor(trim($host['mac']));

            if (empty($vendor['company'])) {
                $log->debug("Mac vendor for {$host['mac']} null");
                $vendor['company'] = '-';
            } else {
                $log->debug("Mac vendor for {$host['mac']} is {$vendor['company']} ");
                if ($vendor['company'] != $host['mac_vendor']) {
                    //TODO sec warn
                    $log->warn("Mac vendor change from {$host['mac_vendor']} to {$vendor['company']} ] updating...");
                    $update['mac_vendor'] = $vendor['company'];
                    $hosts->update($host['id'], $update);
                }
            }
        }
    }
}

function host_access(array $cfg, Hosts $hosts) {
    global $log;

    $db_hosts = $hosts->getEnabled();

    foreach ($db_hosts as $host) {
        if ($host['access_method'] < 1 || empty($host['online'])) {
            continue;
        }

        $ssh_conn_result = [];
        $set = [];
        $set['access_results'] = [];

        $ssh = ssh_connect_host($cfg, $ssh_conn_result, $host);
        if (!$ssh) {
            $log->err("SSH host_access: Cant connect host {$host['ip']}");
            continue;
        }
        $log->info("SSH hosts access: Succesful connect to {$host['ip']}");
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
