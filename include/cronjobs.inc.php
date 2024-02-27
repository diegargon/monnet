<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function check_known_hosts(AppCtx $ctx) {
    $lng = $ctx->getAppLang();
    $db = $ctx->getAppDb();
    $hosts = $ctx->getAppHosts();

    if (!is_object($hosts)) {
        Log::err("hosts is not a object");
        return false;
    }
    Log::debug("Pinging known host");

    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) {
        $new_host_status = [];
        /*
         * Port Scan
         */
        if ($host['check_method'] == 2 && valid_array($host['ports'])) { //TCP
            Log::debug("Pinging host ports {$host['ip']}");
            $ping_ports_result = ping_host_ports($host);
            if ($host['online'] == 1 && $ping_ports_result['online'] == 0) { //recheck
                $ping_ports_result = ping_host_ports($host);
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
            $ping_host_result = ping_known_host($host);
            if ($host['online'] == 1 && $ping_host_result['online'] == 0) {
                //recheck
                $ping_host_result = ping_known_host($host);
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
            } else if ($host['online'] == 1 && $new_host_status['online'] == 0) {
                $new_host_status['online_change'] = utc_date_now();
                Log::logHost('LOG_NOTICE', $host['id'], $host['display_name'] . ': ' . $lng['L_HOST_BECOME_OFF']);
            }

            $hosts->update($host['id'], $new_host_status);
            if ($new_host_status['online'] == 1 && isset($new_host_status['latency']) && $new_host_status['latency'] > 0) {
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

function ping_net(AppCtx $ctx) {
    $hosts = $ctx->getAppHosts();
    $networks = $ctx->getAppNetworks();
    $ping_net_time = microtime(true);
    $timeout = ['sec' => 0, 'usec' => 100000];

    $db_hosts = $hosts->getAll();
    $iplist = $networks->buildIpList();

    //We remove known hosts since we checked in other functions
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
                Log::warn('Failed to get id network for ip ' . $ip);
                $set['network'] = 1;
            } else {
                $set['network'] = $network_id;
            }

            $set['latency'] = round(microtime(true) - $latency, 2);
            $set['last_seen'] = utc_date_now();
            $hostname = get_hostname($ip);
            !empty($hostname) && ($hostname != $ip) ? $set['hostname'] = $hostname : null;

            $hosts->insert($set);
        }
    }

    Log::info('Ping net took ' . (intval(microtime(true) - $ping_net_time)) . ' seconds');
}

function fill_hostnames(Hosts $hosts, int $forceall = 0) {
    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) {
        if (empty($host['hostname']) || $forceall === 1) {
            //Log::debug("Getting hostname {$host['ip']}");
            $hostname = get_hostname($host['ip']);
            if ($hostname !== false && $hostname != $host['ip']) {
                $update['hostname'] = $hostname;
                $hosts->update($host['id'], $update);
            }
        }
    }
}

function fill_mac_vendors(Hosts $hosts, int $forceall = 0) {
    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) {
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

function check_macs(Hosts $hosts) {

    $known_hosts = $hosts->getknownEnabled();

    Log::info('Checking macs');
    foreach ($known_hosts as $host) {
        $new_mac = get_mac($host['ip']);
        if (!empty($new_mac) && $host['mac'] != $new_mac) {
            $update['mac'] = trim($new_mac);
            $hosts->update($host['id'], $update);
        }
    }
}

function host_access(array $cfg, Hosts $hosts) {

    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) {
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
