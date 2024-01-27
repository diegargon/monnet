<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);

require_once('include/common.inc.php');
require_once('include/usermode.inc.php');

$tdata = [];
$data = [];
$data['conn'] = 'success';
if ($user->getId() > 0) {
    $data['login'] = 'success';
} else {
    $data['login'] = 'fail';
    print(json_encode($data));
    exit();
}
$frontend = new Frontend($cfg, $lng);
$tdata['theme'] = $cfg['theme'];

$command = Filters::getString('order');
$command_value = Filters::getString('order_value');

if (!empty($command) && !empty($command_value)) {
    $data['command_receive'] = $command;
    $data['command_value'] = $command_value;
}

/* Remove host */
if ($command === 'remove_host' && is_numeric($command_value)) {
    $hosts->remove($command_value);
    //no host_details
    $user->setPref('host_details', 0);
    $data['host_details'] = '';
}

/* Set show/hide host-details */
if ($command === 'host-details' && is_numeric($command_value)) {
    $host_details = get_host_detail_view_data($db, $cfg, $hosts, $user, $lng, $command_value);
    if ($host_details) {
        $tdata['host_details'] = $host_details;
        $data['host_details']['cfg']['place'] = "#left_container";
        if (!empty($host_details['ping_stats'])) {
            $tdata['host_details']['ping_graph'] = $frontend->getTpl('chart-time', $host_details['ping_stats']);
        }           
        $tdata['host_details']['host_logs'] = '-';//force display test
        unset($tdata['host_details']['ping_stats']);
        $data['host_details']['data'] = $frontend->getTpl('host-details', $tdata);
    }
}

/* Set show/hide highlight hosts */
if ($user->getPref('show_highlight_hosts_status')) {
    $hosts_view = get_hosts_view_data($cfg, $hosts, $user, $lng, 1);
    if ($hosts_view) {
        $tdata['hosts'] = $hosts_view;
        $tdata['container-id'] = 'highlight-hosts';
        $tdata['head-title'] = $lng['L_HIGHLIGHT_HOSTS'];
        $data['highlight_hosts']['data'] = $frontend->getTpl('hosts-min', $tdata);
        $data['highlight_hosts']['cfg']['place'] = '#left_container';
    }
}
if ($user->getPref('show_other_hosts_status')) {
    $hosts_view = get_hosts_view_data($cfg, $hosts, $user, $lng, 0);
    if ($hosts_view) {
        $tdata['hosts'] = $hosts_view;
        $tdata['container-id'] = 'other-hosts';
        $tdata['head-title'] = $lng['L_OTHERS'];
        $data['other_hosts']['cfg']['place'] = '#left_container';
        $data['other_hosts']['data'] = $frontend->getTpl('hosts-min', $tdata);
    }
}
if ($command == 'saveNote' && !empty($command_value)) {
    
}

/* Power ON/OFF  & Reboot */
if ($command == 'power_on' && !empty($command_value) && is_numeric($command_value)) {
    $host = $hosts->getHostById($command_value);

    if (!empty($host['mac'])) {
        sendWOL($host['mac']);
    } else {
        $log->warn("Host {$host['ip']} has not mac address");
    }
}
if ($command == 'power_off' && !empty($command_value) && is_numeric($command_value)) {
    $result = $db->select('cmd', 'cmd_id', ['cmd_type' => 2, 'hid' => $command_value], 'LIMIT 1');
    $coincidence = $db->fetchAll($result);

    if (empty($coincidence)) {
        $db->insert('cmd', ['cmd_type' => 2, 'hid' => $command_value]);
    }
}
if ($command == 'reboot' && !empty($command_value) && is_numeric($command_value)) {

    $result = $db->select('cmd', 'cmd_id', ['cmd_type' => 1, 'hid' => $command_value], 'LIMIT 1');
    $coincidence = $db->fetchAll($result);

    if (empty($coincidence)) {
        $db->insert('cmd', ['cmd_type' => 1, 'hid' => $command_value]);
    }
}

/*  -   */
//$log->debug(print_r($data,true));
print json_encode($data);
