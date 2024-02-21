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

$tdata = [];
$data = [];
$data['conn'] = 'success';
$force_host_reload = 0;

if ($user->getId() > 0) {
    $data['login'] = 'success';
} else {
    $data['login'] = 'fail';
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
$data['command_sucess'] = 0;
$data['command_error_msg'] = '';

/* Remove host */
if ($command === 'remove_host' && is_numeric($command_value)) {
    $hosts->remove($command_value);
    //no host_details
    $user->setPref('host_details', 0);
    $data['host_details'] = '';
    $command = $command_value = '';
    $data['command_sucess'] = 1;
}

if ($command == 'network_select' && !empty($command_value) && is_numeric($command_value)) {
    $pref_name = 'network_select_' . $command_value;
    $user->setPref($pref_name, 1);
    $data['command_sucess'] = 1;
    $force_host_reload = 1;
}
if ($command == 'network_unselect' && !empty($command_value) && is_numeric($command_value)) {
    $pref_name = 'network_select_' . $command_value;
    $user->setPref($pref_name, 0);
    $data['command_sucess'] = 1;
    $force_host_reload = 1;
}

if ($command == 'setCheckPorts' && isset($command_value) && !empty($object_id)) {
    // 1 ping 2 TCP/UDP
    ($command_value == 0) ? $value = 1 : $value = 2;

    $hosts->update($object_id, ['check_method' => $value]);
    $data['command_sucess'] = 1;
}

if ($command == 'submitScanPorts' && !empty($object_id) && is_numeric($object_id)) {
    $sucess = 0;
    if (!empty($command_value)) {
        $valid_ports = validatePortsInput(trim($command_value));
        if (valid_array($valid_ports)) {
            if (($encoded_ports = json_encode($valid_ports))) {
                $db->update('hosts', ['ports' => $encoded_ports], ['id' => $object_id]);
                $total_elements = count($valid_ports) - 1;
                $sucess = '';
                foreach ($valid_ports as $index => $port) {
                    $sucess .= $port['n'] . '/';
                    $sucess .= ($port['port_type'] === 1) ? 'tcp' : 'udp';
                    $sucess .= '/' . $port['name'];
                    $sucess .= ($index === $total_elements) ? '' : ',';
                }
            }
        }
    }
    $data['command_sucess'] = $sucess;
}

if ($command == 'submitTitle' && !empty($object_id) && is_numeric($object_id)) {
    $sucess = 0;
    if (!empty($command_value)) {
        $hosts->update($object_id, ['title' => $command_value]);
        $sucess = 1;
    }
    $data['command_sucess'] = $sucess;
    $force_host_reload = 1;
}

// Change Host Cat
if ($command == 'submitCat' && !empty($object_id) && is_numeric($object_id)) {
    $sucess = 0;
    if (!empty($command_value)) {
        $hosts->update($object_id, ['category' => $command_value]);
        $sucess = 1;
    }
    $data['command_sucess'] = $sucess;
    $data['response_msg'] = 'Category changed to ' . $command_value;
    $force_host_reload = 1;
}


if ($command == 'submitManufacture' && !empty($object_id) && is_numeric($object_id)) {
    $sucess = 0;
    if (!empty($command_value)) {
        $hosts->update($object_id, ['manufacture' => $command_value]);
        $sucess = 1;
    }
    $data['command_sucess'] = $sucess;
    $data['response_msg'] = 'Manufacture changed to ' . $command_value;
    $force_host_reload = 1;
}

if ($command == 'submitOS' && !empty($object_id) && is_numeric($object_id)) {
    $sucess = 0;
    if (!empty($command_value)) {
        $hosts->update($object_id, ['os' => $command_value]);
        $sucess = 1;
    }
    $data['command_sucess'] = $sucess;
    $data['response_msg'] = 'OS changed to ' . $command_value;
    $force_host_reload = 1;
}

if ($command == 'submitSystemType' && !empty($object_id) && is_numeric($object_id)) {
    $sucess = 0;
    if (!empty($command_value)) {
        $hosts->update($object_id, ['system_type' => $command_value]);
        $sucess = 1;
    }
    $data['command_sucess'] = $sucess;
    $data['response_msg'] = 'System Type changed to ' . $command_value;
    $force_host_reload = 1;
}


/* Show Host Cat */

if ($command == 'show_host_cat' && isset($command_value) && is_numeric($command_value)) {
    $db->toggleField('categories', 'on', ['id' => $command_value]);
    !isset($categories) ? $categories = new Categories($cfg, $lng, $db) : null;
    $tdata['hosts_categories'] = $categories->prepareCats(1);
    //Networks dropdown
    $networks_q = $db->selectAll('networks', ['disable' => 0]);
    $networks = $db->fetchAll($networks_q);

    $networks_selected = 0;
    foreach ($networks as &$net) {
        $net_set = $user->getPref('network_select_' . $net['id']);
        if (($net_set) || $net_set === false) {
            $net['selected'] = 1;
            $networks_selected++;
        }
    }
    $tdata['networks'] = $networks;
    $tdata['networks_selected'] = $networks_selected;

    //
    $data['categories_host']['data'] = $frontend->getTpl('categories-host', $tdata);
    $data['categories_host']['cfg']['place'] = '#left_container';
    $data['command_sucess'] = 1;
    $force_host_reload = 1;
}
$highlight_hosts_count = 0;

if ((empty($command) && empty($command_value)) || $force_host_reload) {
    /* Set show/hide highlight hosts */
    if ($user->getPref('show_highlight_hosts_status')) {
        $hosts_view = get_hosts_view_data($cfg, $hosts, $user, $lng, 1);
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
        //$hosts_view = get_hosts_view_data($cfg, $hosts, $user, $lng, 0);
        !isset($categories) ? $categories = new Categories($cfg, $lng, $db) : null;
        $hosts_view = get_listcat_hosts($cfg, $hosts, $user, $lng, $categories);
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
    $host_details = get_host_detail_view_data($db, $cfg, $hosts, $user, $lng, $host_id);
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
                    $loglevelname = $log->getLogLevelName($term_log['level']);
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
        $data['command_sucess'] = 1;
    } else {
        $data['command_error_msg'] .= 'Invalid host-details array';
    }
}

if ($command == 'saveNote' && !empty($command_value) && !empty($object_id)) {
    $set = ['content' => urldecode($command_value)];
    $where = ['id' => $object_id];

    $db->update('notes', $set, $where, 'LIMIT 1');
    $data['command_sucess'] = 1;
}

if ($command == 'setHighlight' && !empty($object_id)) {

    $value = (empty($command_value)) ? 0 : 1;

    $hosts->update($object_id, ['highlight' => $value]);
    $data['command_sucess'] = 1;
    $data['response_msg'] = 'Changed to ' . $value;
}

if ($command == 'removeBookmark' && !empty($command_value) && is_numeric($command_value)) {
    $db->delete('items', ['id' => $command_value], 'LIMIT 1');
    $data['command_sucess'] = 1;
}

/* Power ON/OFF  & Reboot */
if ($command == 'power_on' && !empty($command_value) && is_numeric($command_value)) {
    $host = $hosts->getHostById($command_value);

    if (valid_array($host) && !empty($host['mac'])) {
        sendWOL($host['mac']);
        $data['command_sucess'] = 1;
    } else {
        $err_msg = "Host {$host['ip']} has not mac address";
        $log->warning($err_msg);
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
    $data['command_sucess'] = 1;
}

if ($command == 'change_bookmarks_tab' && !empty($command_value)) {
    $user->setPref('default_bookmarks_tab', $command_value);
    $data['command_sucess'] = 1;
}
/* ALWAYS */

$logs = [];

$host_logs = $log->getLoghosts($cfg['term_max_lines']);
if (valid_array($host_logs)) {
    $logs = $host_logs;
}
if ($cfg['term_show_system'] && $cfg['log_to_db']) {
    $system_logs = $log->getSystemDBLogs($cfg['term_max_lines']);
    if (valid_array($system_logs)) {
        $logs = array_merge($logs, $system_logs);
    }
}

usort($logs, function ($a, $b) {
    $dateA = strtotime($a['date']);
    $dateB = strtotime($b['date']);

    return ($dateA < $dateB) ? 1 : -1;
});

$term_logs = array_slice($logs, 0, $cfg['term_max_lines']);
if (valid_array($term_logs)) {
    $log_lines = [];
    foreach ($term_logs as $term_log) {
        $date = datetime_string_format($term_log['date'], $cfg['term_date_format']);
        $loglevelname = $log->getLogLevelName($term_log['level']);
        $loglevelname = str_replace('LOG_', '', $loglevelname);
        $log_lines[] = $date . '[' . $loglevelname . ']' . $term_log['msg'];
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
//$log->debug(print_r($data,true));
//print json_encode($data);
print json_encode($data, JSON_UNESCAPED_UNICODE);
