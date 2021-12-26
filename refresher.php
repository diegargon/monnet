<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
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
    $db->delete('hosts', ['id' => $command_value], 'LIMIT 1');
    //no host_details
    $user->setPref('host_details', 0);
    $data['host_details'] = '';
}

/* Set show/hide host-details */
if ($command === 'host-details' && is_numeric($command_value)) {
    $user->setPref('host_details', $command_value);
}
if ($user->getPref('host_details')) {
    $tdata['host_details'] = get_host_detail_view_data($cfg, $db, $user, $lng, $user->getPref('host_details'));
    $data['host_details']['data'] = $frontend->getTpl('host-details', $tdata);
    $data['host_details']['cfg']['place'] = "#left_container";
}

/* Set show/hide highlight hosts */
if ($user->getPref('show_hightlight_hosts_status')) {
    $tdata['hosts'] = get_hosts_view_data($cfg, $db, $user, $lng, 1);
    $data['highlight_hosts']['data'] = $frontend->getTpl('hosts', $tdata);
    $data['highlight_hosts']['cfg']['place'] = '#left_container';
}
if ($user->getPref('show_other_hosts_status')) {
    $tdata['other_hosts'] = get_hosts_view_data($cfg, $db, $user, $lng, 0);
    $data['other_hosts']['data'] = $frontend->getTpl('other-hosts', $tdata);
    $data['other_hosts']['cfg']['place'] = '#left_container';
}


/* Power ON/OFF  & Reboot */
if ($command == 'power_on' && !empty($command_value) && is_numeric($command_value)) {
    send_magic_packet($command_value);
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

print json_encode($data);
