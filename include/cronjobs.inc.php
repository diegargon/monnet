<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function check_known_hosts(Database $db) {
    $hosts = get_known_hosts($db);

    if (valid_array($hosts)) {

        foreach ($hosts as $host) {
            if ($host['check_method'] == 2) { //TCP
                $host_new_status = ping_host_ports($host);
                if ($host_new_status !== false) {
                    update_host($db, $host_new_status);
                }
            } else { //Ping
                ping_known_host($db, $host);
            }
        }
    }
}

function ping_known_host(Database $db, array $host) {

    $timeout = ['sec' => 0, 'usec' => 500000];

    if (is_local_ip($host['ip'])) {
        $timeout = ['sec' => 0, 'usec' => 200000];
    }

    $ip_status = ping($host['ip'], $timeout);
    $set = [];

    $mac = get_mac($host['ip']);
    if ($mac) {
        $set['mac'] = $mac;
    }
    $clilog = $ip_status;
    unset($clilog['latency']);

    $set['clilog'] = json_encode($clilog);
    if ($ip_status['isAlive']) {
        $set['online'] = 1;
        $set['last_seen'] = time();
        $set['latency'] = $ip_status['latency'];
        $db->update('hosts', $set, ['id' => ['value' => $host['id']]], 'LIMIT 1');
    } else if ($ip_status['isAlive'] == 0 && $host['online'] == 1) {
        $set['online'] = 0;
        $set['latency'] = $ip_status['latency'];
        $db->update('hosts', $set, ['id' => ['value' => $host['id']]], 'LIMIT 1');
    }
}

function ping_net(array $cfg, Database $db) {
    $hosts = get_hosts($db);
    $iplist = get_iplist($cfg['net']);

    foreach ($iplist as $ip) {
        $latency = microtime(true);
        $jump = false;
        $ip = trim($ip);

        foreach ($hosts as $host) {
            if ($host['ip'] == $ip) { //Jump know ips since we check in other fuc
                $jump = true;
            }
        }
        if ($jump) {
            continue;
        }
        $ip_status = ping($ip);
        $set = [];

        $mac = get_mac($ip);
        if ($mac) {
            $set['mac'] = $mac;
        }

        if ($ip_status['isAlive']) {
            $set['ip'] = $ip;
            $set['online'] = 1;
            $set['latency'] = microtime(true) - $latency;
            $results = $db->query('SELECT `id`,`mac`,`online` FROM hosts WHERE ip=\'' . $ip . '\' LIMIT 1');
            $host_results = $db->fetchAll($results);
            if (!empty($host_results) && is_array($host_results) && count($host_results) > 0) {
                if ($host_results[0]['online'] != 1 || $host_results[0]['mac'] != $mac) {
                    $db->update('hosts', $set, ['id' => ['value' => $host_results[0]['id']]], 'LIMIT 1');
                }
            } else {
                $db->insert('hosts', $set);
            }
        } else {
            $results = $db->query('SELECT `id`,`online` FROM hosts WHERE ip=\'' . $ip . '\'  LIMIT 1');
            $host_results = $db->fetchAll($results);

            if (!empty($host_results) && is_array($host_results) && count($host_results) > 0) {
                if ($host_results[0]['online'] == 1) {
                    $set['online'] = 0;
                    $set['latency'] = microtime(true) - $latency;
                    $db->update('hosts', $set, ['id' => ['value' => $host_results[0]['id']]]);
                }
            }
        }
    }
}

function fill_hostnames(Database $db, int $only_missing = 0) {
    $hosts = get_hosts($db);

    foreach ($hosts as $host) {
        if (empty($host['hostname']) || $only_missing === 0) {
            $hostname = get_hostname($host['ip']);
            if ($hostname !== false && $hostname != $host['ip']) {
                $db->update('hosts', ['hostname' => $hostname], ['ip' => ['value' => $host['ip']]], 'LIMIT 1');
            }
        }
    }
}

function host_access($cfg, $db) {
    $hosts = get_known_hosts($db);

    foreach ($hosts as $host) {
        if ($host['access_method'] < 1 || empty($host['online'])) {
            continue;
        }

        $ssh_conn_result = [];
        $set = [];
        $set['access_results'] = [];

        $ssh = ssh_connect_host($cfg, $ssh_conn_result, $host);
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

        //var_dump($result);
        if (!empty($results['hostname'])) {
            $set['hostname'] = $results['hostname'];
            unset($results['hostname']);
        }
        //unset($results['motd']);
        $set['access_results'] = $db->escape(json_encode($results));

        $db->update('hosts', $set, ['id' => $host['id']]);
    }
}
