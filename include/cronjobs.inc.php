<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function check_known_hosts(Log $log, Database $db, Hosts $hosts) {
    global $lng;

    if (!is_object($hosts)) {
        $log->err("hosts is not a object");
        return false;
    }
    $log->debug("Pinging known host");

    $db_hosts = $hosts->getknownEnabled();

    //TODO one update/insert
    foreach ($db_hosts as $host) {
        $host_status = [];

        if ($host['check_method'] == 2 && valid_array($host['ports'])) { //TCP
            $log->debug("Pinging host ports {$host['ip']}");
            $ping_host_result = ping_host_ports($host);
            if ($ping_host_result['online'] == 0) {
                //recheck
                $ping_host_result = ping_host_ports($host);
            }
            //Ports are down, check host with ping
            if ($ping_host_result['online'] == 0) {
                $host_ping = ping($host['ip'], ['sec' => 0, 'usec' => 100000]);
                if ($host_ping['isAlive']) {
                    $ping_host_status['online'] = 1;
                    $ping_host_status = $host_ping['latency'];
                    $ping_host_status['last_seen'] = utc_date_now();
                }
            }
            (valid_array($ping_host_result)) ? $host_status = $ping_host_result : null;

            if ($ping_host_result['online'] == 1 && $host['online'] == 0) {
                $log->logHost('LOG_NOTICE', $host['id'], $host['display_name'] . ': ' . $lng['L_HOST_BECOME_ON']);
            } else if ($ping_host_result['online'] == 0 && $host['online'] == 1) {
                $log->logHost('LOG_NOTICE', $host['id'], $host['display_name'] . ': ' . $lng['L_HOST_BECOME_OFF']);
            }
        } else { //Ping
            if ($host['check_method'] == 2 && !valid_array($host['ports'])) {
                $log->warning("No check ports for host {$host['id']}:{$host['display_name']}, pinging.");
            }
            $ping_host_result = ping_known_host($host);
            if ($host['online'] == 1 && $ping_host_result['online'] == 0) {
                //recheck
                $ping_host_result = ping_known_host($host);
            }
            if ($ping_host_result['online'] == 1 && $host['online'] == 0) {
                $log->logHost('LOG_NOTICE', $host['id'], $host['display_name'] . ': ' . $lng['L_HOST_BECOME_ON']);
            } else if ($ping_host_result['online'] == 0 && $host['online'] == 1) {
                $log->logHost('LOG_NOTICE', $host['id'], $host['display_name'] . ': ' . $lng['L_HOST_BECOME_OFF']);
            }
            (valid_array($ping_host_result)) ? $host_status = $ping_host_result : null;
        }
        if (valid_array($host_status) && $host_status['online'] && empty($host['mac'])) {
            $mac = get_mac($host['ip']);
            $mac ? $host_status['mac'] = $mac : null;
        }
        if (valid_array($host_status)) {
            defined('DUMP_VARS') ? $log->debug("Dumping host_status: " . print_r($host_status, true)) : null;
            $hosts->update($host['id'], $host_status);
            if (isset($host_status['latency']) && $host_status['latency'] > 0) {
                $ping_latency = $host_status['latency'];
                $set_ping_stats = ['date' => utc_date_now(), 'type' => 1, 'host_id' => $host['id'], 'value' => $ping_latency];
                $db->insert('stats', $set_ping_stats);
            }
        } else {
            $log->warning("Known host ping status error {$host['id']}:{$host['display_name']}");
        }
    }
    $log->debug('Finish check_known_hosts');
}

function ping_net(Database $db, Hosts $hosts) {
    global $log;

    $query = $db->selectAll('networks', ['scan' => 1, 'disable' => 0]);
    $networks = $db->fetchAll($query);
    $timeout = ['sec' => 0, 'usec' => 100000];

    $db_hosts = $hosts->getAll();

    $iplist = build_iplist($networks);

    //We remove known hosts since we checked in other functions
    foreach ($iplist as $kip => $vip) {
        $vip = trim($vip);
        $iplist[$kip] = $vip;
        foreach ($db_hosts as $host) {
            if ($host['ip'] == $vip) {
                unset($iplist[$kip]);
                //Temporaly Check network for UPDATE
                $idNetwork = get_network_id($host['ip'], $networks);
                if ($idNetwork == false) {
                    $log->warning('Failed to get id network for ip ' . $host['ip']);
                    $set['network'] = 1;
                } else {
                    $set['network'] = $idNetwork;
                }
                if (valid_array($set) && ($idNetwork != $host['network'])) {
                    $db->update('hosts', $set, ['id' => $host['id']]);
                    $log->warning('Update host network ' . $host['diplay_name'] . ' from ' . $host['network'] . ' to ' . $idNetwork);
                }
            }
        }
    }

    foreach ($iplist as $ip) {
        $latency = microtime(true);

        $ip_status = ping($ip, $timeout);
        $set = [];

        if ($ip_status['isAlive']) {
            $mac = trim(get_mac($ip));
            if ($mac) {
                $set['mac'] = $mac;
                $mac_info = get_mac_vendor($mac);
                (!empty($mac_info['company'])) ? $set['mac_vendor'] = $mac_info['company'] : $set['mac_vendor'] = '-';
            }
            $set['ip'] = $ip;
            $set['online'] = 1;

            $idNetwork = get_network_id($ip, $networks);
            if ($idNetwork == false) {
                $log->warn('Failed to get id network for ip ' . $ip);
                $set['network'] = 1;
            } else {
                $set['network'] = $idNetwork;
            }

            $set['latency'] = round(microtime(true) - $latency, 2);
            $set['last_seen'] = utc_date_now();
            $hostname = get_hostname($ip);
            !empty($hostname) && ($hostname != $ip) ? $set['hostname'] = $hostname : null;

            $hosts->insert($set);
        }
    }
}

function fill_hostnames(Hosts $hosts, int $forceall = 0) {
    global $log;

    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) {
        if (empty($host['hostname']) || $forceall === 1) {
            $log->debug("Getting hostname {$host['ip']}");
            $hostname = get_hostname($host['ip']);
            if ($hostname !== false && $hostname != $host['ip']) {
                $update['hostname'] = $hostname;
                $hosts->update($host['id'], $update);
            }
        }
    }
}

function fill_mac_vendors(Hosts $hosts, int $forceall = 0) {
    global $log;

    $db_hosts = $hosts->getknownEnabled();

    foreach ($db_hosts as $host) {
        $vendor = [];
        $update = [];

        if ((!empty($host['mac'])) &&
                (empty($host['mac_vendor']) || $forceall === 1)
        ) {
            $log->debug("Getting mac vendor for {$host['display_name']}");
            $vendor = get_mac_vendor_local(trim($host['mac']));
            if (empty($vendor)) {
                $log->debug("Local lookup fail, checking mac online");
                $vendor = get_mac_vendor(trim($host['mac']));
            }

            if (empty($vendor['company'])) {
                $log->debug("Mac vendor for {$host['mac']} is null");
                (empty($host['mac_vendor'])) ? $update['mac_vendor'] = '-' : null;
            } else {
                if ($vendor['company'] != $host['mac_vendor']) {
                    $log->warning("Mac vendor change from {$host['mac_vendor']} to {$vendor['company']} ] updating...");
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
    global $log;

    $known_hosts = $hosts->getknownEnabled();

    $log->info('Checking macs');
    foreach ($known_hosts as $host) {
        $new_mac = get_mac($host['ip']);
        if (!empty($new_mac) && $host['mac'] != $new_mac) {
            $update['mac'] = trim($new_mac);
            $hosts->update($host['id'], $update);
        }
    }
}

function host_access(array $cfg, Hosts $hosts) {
    global $log;

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
