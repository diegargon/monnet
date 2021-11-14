<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

/*
  function get_highlight_hosts(Database $db) {
  return get_hosts($db, 1);
  }
 *
 */

function get_known_hosts(Database $db) {
    return get_hosts($db);
}

function get_hosts(Database $db, int $highlight = null) {
    $query_ports = 'SELECT * FROM ports';
    $results = $db->query($query_ports);
    $hosts_ports = $db->fetchAll($results);

    $query_hosts = 'SELECT * FROM hosts WHERE disable = 0';
    if ($highlight === 0 || $highlight === 1) {
        $query_hosts .= ' AND highlight=' . $highlight;
    }

    $results = $db->query($query_hosts);
    if ($results) {
        $hosts = $db->fetchAll($results);

        foreach ($hosts as $k_host => $host) {
            $ports = [];
            foreach ($hosts_ports as $host_port) {
                if ($host_port['hid'] == $host['id']) {
                    $ports[] = $host_port;
                }
            }
            if (count($ports) > 0) {
                $hosts[$k_host]['ports'] = $ports;
            }
        }

        return $hosts;
    }

    return false;
}

function update_host(Database $db, array $host) {

    $remove_fields = [
        'id', 'title', 'hostname', 'mac', 'mac_vendor', 'ip', 'highlight', 'system', 'distributor', 'codename', 'version', 'img_ico', 'weight',
        'status', 'online', 'wol', 'timeout', 'check_method', 'access_method', 'disable', 'clilog', 'warn', 'ports', 'comment', 'updated'
    ];
    $host_log = $host;
    foreach ($remove_fields as $key) {
        unset($host_log[$key]);
    }

    if (!empty($host_log)) {
        $host['clilog'] = json_encode($host_log);
    } else {
        $host['clilog'] = null;
    }

    if (is_local_ip($host['ip'])) {
        $mac = get_mac($host['ip']);
        if ($mac) {
            $set_host['mac'] = $mac;
        }
    }

    $set_host['online'] = $host['online'];
    $set_host['clilog'] = $host['clilog'];

    $db->update('hosts', $set_host, ['id' => ['value' => $host['id']]], 'LIMIT 1');

    foreach ($host['ports'] as $port) {
        empty($port['clilog']) ? $port['clilog'] = 'null' : null;
        $query = 'UPDATE ports SET online = ' . $port['online'] . ', clilog = ' . $port['clilog'] . ' WHERE id=' . $port['id'] . ' AND hid=' . $port['hid'] . ' LIMIT 1';
        $db->query($query);
    }
}

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

    $set['clilog'] = json_encode($ip_status);
    if ($ip_status['isAlive'] && $host['online'] != 1) {
        $set['online'] = 1;
        $db->update('hosts', $set, ['id' => ['value' => $host['id']]], 'LIMIT 1');
    } else if ($ip_status['isAlive'] == 0 && $host['online'] == 1) {
        $set['online'] = 0;
        $db->update('hosts', $set, ['id' => ['value' => $host['id']]], 'LIMIT 1');
    }
}

function ping_net(array $cfg, Database $db) {
    $hosts = get_hosts($db);
    $iplist = get_iplist($cfg['net']);

    foreach ($iplist as $ip) {

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
            $results = $db->query('SELECT `id`,`mac`,`online` FROM hosts WHERE ip=\'' . $ip . '\' LIMIT 1');
            $host_results = $db->fetchAll($results);
            if (!empty($host_results) && is_array($host_results) && count($host_results) > 0) {
                if ($host_results[0]['online'] != 1 || $host_results[0]['mac'] != $mac) {
                    $set['online'] = 1;
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
