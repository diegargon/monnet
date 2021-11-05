<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function get_wellknown_hosts(Database $db) {
    return get_hosts($db, 1);
}

function get_hosts(Database $db, int $wellknown = null) {
    $query_ports = 'SELECT * FROM ports';
    $results = $db->query($query_ports);
    $hosts_ports = $db->fetchAll($results);

    $query_hosts = 'SELECT * FROM hosts WHERE disable = 0';
    if ($wellknown === 0 || $wellknown === 1) {
        $query_hosts .= ' AND wellknown=' . $wellknown;
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
        'id', 'title', 'host', 'os', 'distributor', 'codename', 'version', 'ico', 'weight',
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

function check_wellknown_hosts(Database $db) {
    $hosts = get_wellknown_hosts($db);

    ping_ports($hosts);

    foreach ($hosts as $host_id => $host) {
        update_host($db, $host_id, $host);
    }
}

function ping_net(array $cfg, Database $db) {

    $hosts = get_wellknown_hosts($db, 0);

    $iplist = get_iplist($cfg['net']);

    //Remove wellknown hosts (check in another func)

    foreach ($iplist as $kip => $ip) {
        foreach ($hosts as $host) {
            if ($host['host'] == $ip) {
                unset($iplist[$kip]);
            }
        }
    }


    foreach ($iplist as $ip) {
        $ip_status = ping($ip);
        if ($ip_status['isAlive']) {
            $set['host'] = $ip;
            $set['online'] = 1;
            $results = $db->query('SELECT `id`,`online` FROM hosts WHERE host=\'' . $ip . '\' LIMIT 1');
            $host_results = $db->fetchAll($results);
            if (!empty($host_results) && is_array($host_results) && count($host_results) > 0) {
                if ($host_results[0]['online'] !== 1) {
                    $set['online'] = 1;
                    $db->update('hosts', $set, ['id' => ['value' => $host_results[0]['id']]], 'LIMIT 1');
                }
            } else {
                $db->insert('hosts', $set);
            }
        } else {
            $results = $db->query('SELECT `id`,`online` FROM hosts WHERE host=\'' . $ip . '\'  LIMIT 1');
            $host_results = $db->fetchAll($results);
            if (!empty($host_results) && is_array($host_results) && count($host_results) > 0) {
                if ($host_results[0]['online'] === 1) {
                    $set['online'] = 0;
                    $db->update('hosts', $set, ['id' => ['value' => $host_results[0]['id']]]);
                }
            }
        }
    }
}
