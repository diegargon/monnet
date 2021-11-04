<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function get_hosts(Database $db) {
    $query_ports = 'SELECT * FROM ports';
    $results = $db->query($query_ports);
    $hosts_ports = $db->fetchAll($results);

    $query_hosts = 'SELECT * FROM hosts WHERE disable = 0';
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

function check_hosts(Database $db) {
    $hosts = get_hosts($db);

    ping_ports($hosts);

    foreach ($hosts as $host_id => $host) {
        update_host($db, $host_id, $host);
    }
}
