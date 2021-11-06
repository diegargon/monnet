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
$data['conn'] = 'successful';
if ($user->getId() > 0) {
    $data['login'] = 'successful';
} else {
    $data['login'] = 'fail';
    print(json_encode($data));
    exit();
}
$frontend = new Frontend($cfg);
$tdata['theme'] = $cfg['theme'];

$command = Filters::getString('order');
$command_value = Filters::getString('order_value');

if (!empty($command) && !empty($command_value)) {
    $data['command_receive'] = $command;
    $data['command_value'] = $command_value;
}
if ($command === 'host-details' && is_numeric($command_value)) {
    $user->setPref('host_details', $command_value);
}
if ($user->getPref('host_details')) {
    $tdata['host_details'] = get_host_detail_view_data($cfg, $db, $user, $lng, $user->getPref('host_details'));
    $data['host_details'] = $frontend->getTpl('host-details', $tdata);
}

if ($user->getPref('show_hightlight_hosts_status')) {
    $tdata['hosts'] = get_host_view_data($cfg, $db, $user, $lng, 1);
    $data['highlight_hosts'] = $frontend->getTpl('hosts', $tdata);
}
if ($user->getPref('show_rest_hosts_status')) {
    $tdata['hosts'] = get_host_view_data($cfg, $db, $user, $lng, 0);
    $data['rest_hosts'] = $frontend->getTpl('rest-hosts', $tdata);
}

print json_encode($data);
