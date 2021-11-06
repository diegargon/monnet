<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function get_highlight_hosts(Database $db) {
    return get_hosts($db, 1);
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
        //use host id for array key
        $hosts = [];
        $_hosts = $db->fetchAll($results);

        foreach ($_hosts as $_host) {
            $host_id = $_host['id'];
            unset($_host['id']);
            $hosts[$host_id] = $_host;
        }
        //add ports
        foreach ($hosts_ports as $host_port) {
            $hosts[$host_port['hid']]['ports'][] = $host_port;
        }

        return $hosts;
    }

    return false;
}

function update_host(Database $db, int $hid, array $host) {
    $remove_fields = [
        'id', 'title', 'hostname', 'ip', 'highlight', 'os', 'distributor', 'codename', 'version', 'ico', 'weight',
        'status', 'online', 'access_method', 'disable', 'clilog', 'ports'
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

    $set_host = [
        'online' => $host['online'],
        'clilog' => $host['clilog'],
    ];

    $db->update('hosts', $set_host, ['id' => ['value' => $hid]], 'LIMIT 1');

    foreach ($host['ports'] as $port) {
        empty($port['clilog']) ? $port['clilog'] = 'null' : null;
        $query = 'UPDATE ports SET online = ' . $port['online'] . ', clilog = ' . $port['clilog'] . ' WHERE id=' . $port['id'] . ' AND hid=' . $port['hid'] . ' LIMIT 1';
        $db->query($query);
    }
}

function check_highlight_hosts(Database $db) {
    $hosts = get_highlight_hosts($db);

    ping_ports($hosts);

    foreach ($hosts as $host_id => $host) {
        update_host($db, $host_id, $host);
    }
}

function ping_net(array $cfg, Database $db) {


    $hosts = get_hosts($db);
    $iplist = get_iplist($cfg['net']);

    foreach ($iplist as $ip) {
        $timeout = [];
        $jump = false;

        foreach ($hosts as $host) {
            if ($host['ip'] == $ip) {
                if ($host['highlight']) { //Jump checked in another check_highlight_host
                    $jump = true;
                    break;
                } else {
                    //Increase timeout for assure known host: default in ping  100 000 usec
                    $timeout = ['sec' => 0, 'usec' => 300000];
                }
            }
        }
        if ($jump) {
            continue;
        }
        $ip_status = ping($ip, $timeout);
        $set = [];
        if ($ip_status['isAlive']) {

            $set['ip'] = $ip;
            $set['online'] = 1;
            $results = $db->query('SELECT `id`,`online` FROM hosts WHERE ip=\'' . $ip . '\' LIMIT 1');
            $host_results = $db->fetchAll($results);
            if (!empty($host_results) && is_array($host_results) && count($host_results) > 0) {
                if ($host_results[0]['online'] != 1) {
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
