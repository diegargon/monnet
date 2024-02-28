<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
//header('Content-Type: application/json; charset='. $cfg['charset'].'');
header('Content-Type: application/json; charset=UTF-8');
require_once('include/common.inc.php');
require_once('include/usermode.inc.php');
require_once('include/refresher-func.php');

$tdata = [];
$force_host_reload = 0;

//TODO: We pass target id in  command_value and object_id -> change to always use object_id for that
$data = [
    'conn' => 'success',
    'login' => 'fail',
    'command_receive' => '',
    'command_value' => '',
    'object_id' => '',
    'command_success' => 0,
    'command_error_msg' => '',
    'response_msg' => '',
];

if ($user->getId() > 0) {
    $data['login'] = 'success';
} else {
    print(json_encode($data));
    exit();
}
$frontend = new Frontend($cfg, $lng);
$tdata['theme'] = $cfg['theme'];

$command = Filters::postString('order');
if ($command == 'saveNote') {
    $command_value = trim(Filters::postUTF8('order_value'));
} else if ($command == 'submitScanPorts') {
    $command_value = trim(Filters::postCustomString('order_value', ',/', 255));
} else if ($command == 'setCheckPorts') {
    $command_value = Filters::postInt('order_value');
} else if ($command == 'addNetwork') {
    $command_value = Filters::postCustomString('order_value', ',":.{}');
} else {
    $command_value = trim(Filters::postString('order_value'));
}
$object_id = trim(Filters::postInt('object_id'));

if (!empty($command)) {
    $data['command_receive'] = $command;
}
if (!empty($command_value)) {
    $data['command_value'] = $command_value;
}
if (!empty($object_id)) {
    $data['object_id'] = $object_id;
}

/* Remove host */
if ($command === 'remove_host' && is_numeric($command_value)) {
    $hosts->remove($command_value);
    //no host_details
    $user->setPref('host_details', 0);
    $data['host_details'] = '';
    $command = $command_value = '';
    $data['command_success'] = 1;
}

if ($command == 'network_select' && !empty($command_value) && is_numeric($command_value)) {
    $pref_name = 'network_select_' . $command_value;
    $user->setPref($pref_name, 1);
    $data['command_success'] = 1;
    $force_host_reload = 1;
}
if ($command == 'network_unselect' && !empty($command_value) && is_numeric($command_value)) {
    $pref_name = 'network_select_' . $command_value;
    $user->setPref($pref_name, 0);
    $data['command_success'] = 1;
    $force_host_reload = 1;
}

if ($command == 'setCheckPorts' && isset($command_value) && !empty($object_id)) {
    // 1 ping 2 TCP/UDP
//    ($command_value == 0) ? $value = 1 : $value = 2;

    $hosts->update($object_id, ['check_method' => $command_value]);
    $data['command_success'] = 1;
    $data['response_msg'] = $command_value;
}

if ($command == 'submitHostToken' && !empty($command_value) && is_numeric($command_value)) {
    $token = create_token();
    $hosts->update($command_value, ['token' => $token]);
    $data['response_msg'] = $token;
    $data['command_success'] = 1;
}
if ($command == 'submitScanPorts' && !empty($object_id) && is_numeric($object_id)) {
    $success_msg = '';
    if (!empty($command_value)) {
        $valid_ports = validatePortsInput(trim($command_value));
        if (valid_array($valid_ports)) {
            if (($encoded_ports = json_encode($valid_ports))) {
                $db->update('hosts', ['ports' => $encoded_ports], ['id' => $object_id]);
                $total_elements = count($valid_ports) - 1;
                foreach ($valid_ports as $index => $port) {
                    $success_msg .= $port['n'] . '/';
                    $success_msg .= ($port['port_type'] === 1) ? 'tcp' : 'udp';
                    $success_msg .= '/' . $port['name'];
                    $success_msg .= ($index === $total_elements) ? '' : ',';
                }
            }
        }
    }
    $data['command_success'] = 1;
    $data['response_msg'] = $success_msg;
}

if ($command == 'submitTitle' && !empty($object_id) && is_numeric($object_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($object_id, ['title' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $force_host_reload = 1;
}

// Change Host Cat
if ($command == 'submitCat' && !empty($object_id) && is_numeric($object_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($object_id, ['category' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'Category changed to ' . $command_value;
    $force_host_reload = 1;
}


if ($command == 'submitManufacture' && !empty($object_id) && is_numeric($object_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($object_id, ['manufacture' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'Manufacture changed to ' . $command_value;
    $force_host_reload = 1;
}

if ($command == 'submitOS' && !empty($object_id) && is_numeric($object_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($object_id, ['os' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'OS changed to ' . $command_value;
    $force_host_reload = 1;
}

if ($command == 'submitSystemType' && !empty($object_id) && is_numeric($object_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($object_id, ['system_type' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'System Type changed to ' . $command_value;
    $force_host_reload = 1;
}

/* Show cat Only * */
if ($command == 'show_host_only_cat' && isset($command_value) && is_numeric($command_value)) {
    //!isset($categories) ? $categories = new Categories($ctx) : null;
    $categories = $ctx->getAppCategories();
    $caton = $categories->getOnByType(1);

    if (empty($caton) || count($caton) == 1) {
        $categories->turnAllOn(1);
    } else {
        $categories->turnAllOff(1);
        $categories->toggle($command_value);
    }
}

if ($command == 'show_host_cat' && isset($command_value) && is_numeric($command_value)) {
    $categories = $ctx->getAppCategories();
    $categories->toggle($command_value);
}

if ($command == 'show_host_cat' || $command == 'show_host_only_cat' && isset($command_value) && is_numeric($command_value)) {
    $tdata['hosts_categories'] = $categories->prepareCats(1);
    //Networks dropdown
    $networks_list = $ctx->getAppNetworks()->getNetworks();

    $networks_selected = 0;
    foreach ($networks_list as &$net) {
        $net_set = $user->getPref('network_select_' . $net['id']);
        if (($net_set) || $net_set === false) {
            $net['selected'] = 1;
            $networks_selected++;
        }
    }
    $tdata['networks'] = $networks_list;
    $tdata['networks_selected'] = $networks_selected;

    $data['categories_host']['data'] = $frontend->getTpl('categories-host', $tdata);
    $data['categories_host']['cfg']['place'] = '#left_container';
    $data['command_success'] = 1;
    $force_host_reload = 1;
}

/* /end Host Cat */

if ($command == 'addNetwork' && !empty($command_value)) {
    $decodedJson = json_decode($command_value, true);

    if ($decodedJson === null) {
        $data['command_error_msg'] .= 'JSON Invalid<br/>';
    } else {
        foreach ($decodedJson as $key => $dJson) {
            ($key == 'networkVLAN') ? $key = 'vlan' : null;
            ($key == 'networkScan') ? $key = 'scan' : null;
            ($key == 'networkName') ? $key = 'name' : null;
            $new_network[$key] = trim($dJson);
        }
        $network_plus_cidr = $new_network['network'] . '/' . $new_network['networkCIDR'];
        unset($new_network['networkCIDR']);
        $new_network['network'] = $network_plus_cidr;

        if (!Filters::varNetwork($network_plus_cidr)) {
            $data['command_error_msg'] .= $lng['L_NETWORK'] . ' ' . $lng['L_INVALID'] . '<br/>';
        }
        if (!is_numeric($new_network['vlan'])) {
            $data['command_error_msg'] .= 'VLAN ' . "{$lng['L_MUST_BE']} {$lng['L_NUMERIC']}<br/>";
        }
        if (!is_numeric($new_network['scan'])) {
            $data['command_error_msg'] .= 'Scan ' . "{$lng['L_MUST_BE']} {$lng['L_NUMERIC']}<br/>";
        }
        $networks_list = $ctx->getAppNetworks()->getNetworks();
        foreach ($networks_list as $net) {
            if ($net['name'] == $new_network['name']) {
                $data['command_error_msg'] = 'Name must be unique<br/>';
            }
            if ($net['network'] == $network_plus_cidr) {
                $data['command_error_msg'] = 'Network must be unique<br/>';
            }
        }
        if (empty($data['command_error_msg'])) {
            $ctx->getAppNetworks()->addNetwork($new_network);
            $data['command_success'] = 1;
            $data['response_msg'] = 'ok';
        }
    }
}

$highlight_hosts_count = 0;

if ((empty($command) && empty($command_value)) || $force_host_reload) {
    /* Set show/hide highlight hosts */
    if ($user->getPref('show_highlight_hosts_status')) {
        $hosts_view = get_hosts_view($ctx, 1);
        $highlight_hosts_count = 0;
        if (valid_array($hosts_view)) {
            $highlight_hosts_count = count($hosts_view);
            $tdata = [];
            $tdata['hosts'] = $hosts_view;
            $tdata['container-id'] = 'highlight-hosts';
            $tdata['head-title'] = $lng['L_HIGHLIGHT_HOSTS'];
            $data['highlight_hosts']['data'] = $frontend->getTpl('hosts-min', $tdata);
            $data['highlight_hosts']['cfg']['place'] = '#host_place';
        } else {
            $data['command_error_msg'] .= 'Invalid highlight host data';
        }
    }
    if ($user->getPref('show_other_hosts_status')) {
        $hosts_view = get_hosts_view($ctx);
        if (valid_array($hosts_view)) {
            $shown_hosts_count = count($hosts_view);
            $hosts_totals_count = $hosts->totals;
            $shown_hosts_count = $shown_hosts_count + $highlight_hosts_count;
            $host_on = $hosts->on;
            $host_off = $hosts->off;
            $tdata = [];
            $tdata['hosts'] = $hosts_view;
            $tdata['container-id'] = 'other-hosts';
            $tdata['head-title'] = $lng['L_OTHERS'];
            $data['other_hosts']['cfg']['place'] = '#host_place';
            $data['other_hosts']['data'] = $frontend->getTpl('hosts-min', $tdata);
        } else {
            $data['command_error_msg'] .= 'Invalid or empty other host data' . print_r($hosts_view, true);
        }
    }
}

/* Set show/hide host-details */
if ($command === 'host-details' && is_numeric($command_value)) {
    $host_id = $command_value;
    $host_details = get_host_detail_view_data($ctx, $host_id);
    if (valid_array($host_details)) {
        $tdata['host_details'] = $host_details;
        if (!empty($host_details['ping_stats'])) {
            $tdata['host_details']['ping_graph'] = $frontend->getTpl('chart-time', $host_details['ping_stats']);
        }
        if (!empty($host_details['host_logs'])) {
            if (valid_array($host_details['host_logs'])) {
                $log_lines = [];
                foreach ($host_details['host_logs'] as $term_log) {
                    $date = datetime_string_format($term_log['date'], $cfg['term_date_format']);
                    $loglevelname = Log::getLogLevelName($term_log['level']);
                    $loglevelname = str_replace('LOG_', '', $loglevelname);
                    $log_lines[] = $date . '[' . $loglevelname . ']' . $term_log['msg'];
                }

                $tdata['host_details']['host_logs'] = $frontend->getTpl('term', ['term_logs' => $log_lines, 'host_id' => $host_id]);
            }
        }
        order_name($cfg['os']);
        order_name($cfg['manufacture']);
        order_name($cfg['system_type']);
        unset($tdata['host_details']['ping_stats']);
        $data['host_details']['cfg']['place'] = "#left_container";
        $data['host_details']['data'] = $frontend->getTpl('host-details', $tdata);
        $data['command_success'] = 1;
    } else {
        $data['command_error_msg'] .= 'Invalid host-details array';
    }
}

if ($command == 'saveNote' && !empty($command_value) && !empty($object_id)) {
    //For empty note we must write ':clear' to begin to prevent clean the note
    //if a filter or other return false/empty
    $content = urldecode($command_value);
    if (str_starts_with($content, ":clear")) {
        $content = '';
    }
    $set = ['content' => $content];
    $where = ['id' => $object_id];

    $db->update('notes', $set, $where, 'LIMIT 1');
    $data['command_success'] = 1;
}

if ($command == 'setHighlight' && !empty($object_id)) {

    $value = (empty($command_value)) ? 0 : 1;

    $hosts->update($object_id, ['highlight' => $value]);
    $data['command_success'] = 1;
    $data['response_msg'] = 'Changed to ' . $value;
}

if ($command == 'removeBookmark' && !empty($command_value) && is_numeric($command_value)) {
    $db->delete('items', ['id' => $command_value], 'LIMIT 1');
    $data['command_success'] = 1;
}

/* Power ON/OFF  & Reboot */
if ($command == 'power_on' && !empty($command_value) && is_numeric($command_value)) {
    $host = $hosts->getHostById($command_value);

    if (valid_array($host) && !empty($host['mac'])) {
        sendWOL($host['mac']);
        $data['command_success'] = 1;
    } else {
        $err_msg = "Host {$host['ip']} has not mac address";
        Log::warning($err_msg);
        $data['command_error_msg'] .= $err_msg;
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
    $data['command_success'] = 1;
}

if ($command == 'change_bookmarks_tab' && !empty($command_value)) {
    $user->setPref('default_bookmarks_tab', $command_value);
    $data['command_success'] = 1;
}
/* ALWAYS */

$logs = [];
$type_mark = '';

$host_logs = Log::getLoghosts($cfg['term_max_lines']);
if (valid_array($host_logs)) {
    $logs = $host_logs;
}
if ($cfg['term_show_system_logs'] && $cfg['log_to_db']) {
    $system_logs = Log::getSystemDBLogs($cfg['term_max_lines']);
    if (valid_array($system_logs)) {
        foreach ($logs as $key => $log) {
            $logs[$key]['type_mark'] = '[H]';
        }
        foreach ($system_logs as $key => $system_log) {
            $system_logs[$key]['type_mark'] = '[S]';
        }
        $logs = array_merge($logs, $system_logs);
    }
}

usort($logs, function ($a, $b) {
    $dateA = strtotime($a['date']);
    $dateB = strtotime($b['date']);

    return ($dateA < $dateB) ? 1 : -1;
});
//If we add systems logs probably we exceed the max
if (valid_array($logs) && count($logs) > $cfg['term_max_lines']) {
    $term_logs = array_slice($logs, 0, $cfg['term_max_lines']);
} else {
    $term_logs = $logs;
}
if (valid_array($term_logs)) {
    $log_lines = [];
    foreach ($term_logs as $term_log) {
        $date = datetime_string_format($term_log['date'], $cfg['term_date_format']);
        $loglevelname = Log::getLogLevelName($term_log['level']);
        $loglevelname = str_replace('LOG_', '', $loglevelname);
        $log_lines[] = $date . $term_log['type_mark'] . '[' . $loglevelname . ']' . $term_log['msg'];
    }
    $data['term_logs']['cfg']['place'] = '#center_container';
    $data['term_logs']['data'] = $frontend->getTpl('term', ['term_logs' => $log_lines]);
}

if (!empty($shown_host_count) || !empty($hosts_totals_count)) {
    $data['misc']['totals'] = $lng['L_SHOWED'] . ": $shown_hosts_count | {$lng['L_TOTAL']}: $hosts_totals_count | ";
}
if (!empty($host_on) || !empty($host_off)) {
    $data['misc']['onoff'] = $lng['L_ON'] . ": $host_on | {$lng['L_OFF']}: $host_off | ";
}
$data['misc']['last_refresher'] = $lng['L_REFRESHED'] . ': ' . $user->getDateNow($cfg['datetime_format_min']);

//Todo instance system_prefs
$results = $db->select('prefs', '*', ['uid' => 0]);
$system_prefs = $db->fetchAll($results);
$cli_last_run = 0;

foreach ($system_prefs as $sys_pref) {
    if ($sys_pref['pref_name'] == 'cli_last_run') {
        $cli_last_run = $sys_pref['pref_value'];
        $cli_last_run = utc_to_user_timezone($cli_last_run, $user->getTimezone(), $cfg['datetime_format_min']);
    }
}
$data['misc']['cli_last_run'] = 'CLI ' . strtolower($lng['L_UPDATED']) . ' ' . $cli_last_run;

/* END ALWAYS */


/*  -   */
//Log::debug(print_r($data,true));
//print json_encode($data);
print json_encode($data, JSON_UNESCAPED_UNICODE);
