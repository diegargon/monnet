<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function get_known_hosts(Database $db) {
    return get_hosts($db);
}

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
    $query_hosts .= ' ORDER BY weight';

    $results = $db->query($query_hosts);
    if (!$results) {
        return false;
    }
    $hosts = $db->fetchAll($results);

    //adding ports and convert json details to $host
    foreach ($hosts as $k_host => $host) {
        //Ports
        $ports = [];
        foreach ($hosts_ports as $host_port) {
            if ($host_port['hid'] == $host['id']) {
                $ports[] = $host_port;
            }
        }
        if (count($ports) > 0) {
            $hosts[$k_host]['ports'] = $ports;
        }
        //convert hosts_details
        if (!empty($host['access_results'])) {
            $host_details = json_decode($host['access_results'], true);

            if (!empty($host_details) && is_array($host_details)) {
                foreach ($host_details as $k_host_details => $v_host_details) {
                    if (!empty($v_host_details)) {
                        $hosts[$k_host][$k_host_details] = $v_host_details;
                    }
                }
            }
            //var_dump($host_details);
        }
    }

    return $hosts;
}

function update_host(Database $db, array $host) {

    //TODO better
    $log_remove_fields = [
        'id', 'title', 'hostname', 'mac', 'mac_vendor', 'ip', 'highlight', 'system', 'distributor', 'codename', 'version', 'img_ico', 'weight',
        'status', 'online', 'wol', 'timeout', 'check_method', 'latency', 'last_seen', 'access_method', 'disable', 'clilog', 'warn', 'warn_port', 'ports',
        'comment', 'updated'
    ];

    $host_log = $host;
    foreach ($log_remove_fields as $key) {
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
    isset($host['latency']) ? $set_host['latency'] = $host['latency'] : null;
    !empty($host['last_seen']) ? $set_host['last_seen'] = $host['last_seen'] : null;
    isset($host['online']) ? $set_host['online'] = $host['online'] : null;
    isset($host['warn']) ? $set_host['warn'] = $host['warn'] : null;
    isset($host['warn_port']) ? $set_host['warn_port'] = $host['warn_port'] : null;
    !empty($host['clilog']) ? $set_host['clilog'] = $host['clilog'] : null;

    $db->update('hosts', $set_host, ['id' => ['value' => $host['id']]], 'LIMIT 1');

    foreach ($host['ports'] as $port) {
        empty($port['clilog']) ? $port['clilog'] = 'null' : null;
        $query = 'UPDATE ports SET online = ' . $port['online'] . ', clilog = ' . $port['clilog'] . ' WHERE id=' . $port['id'] . ' AND hid=' . $port['hid'] . ' LIMIT 1';
        $db->query($query);
    }
}
