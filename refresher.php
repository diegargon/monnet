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
    $command_value = Filters::postUTF8('order_value');
} else {
    $command_value = Filters::postString('order_value');
}
$object_id = Filters::postInt('object_id');

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
}

/* Host Cat */

if ($command == 'show_host_cat' && isset($command_value) && is_numeric($command_value)) {
    $db->toggleField('categories', 'on', ['id' => $command_value]);
    !isset($categories) ? $categories = new Categories($cfg, $lng, $db) : null;
    $tdata['hosts_categories'] = $categories->prepareCats(1);
    $data['categories_host']['data'] = $frontend->getTpl('categories_host', $tdata);
    $data['categories_host']['cfg']['place'] = '#left_container';
}
$highlight_hosts_count = 0;

if ((empty($command) && empty($command_value)) || $command == 'show_host_cat') {
    /* Set show/hide highlight hosts */
    if ($user->getPref('show_highlight_hosts_status')) {
        $hosts_view = get_hosts_view_data($cfg, $hosts, $user, $lng, 1);
        $highlight_hosts_count = count($hosts_view);
        if ($hosts_view) {
            $tdata = [];
            $tdata['hosts'] = $hosts_view;
            $tdata['container-id'] = 'highlight-hosts';
            $tdata['head-title'] = $lng['L_HIGHLIGHT_HOSTS'];
            $data['highlight_hosts']['data'] = $frontend->getTpl('hosts-min', $tdata);
            $data['highlight_hosts']['cfg']['place'] = '#host_place';
        }
    }
    if ($user->getPref('show_other_hosts_status')) {
        //$hosts_view = get_hosts_view_data($cfg, $hosts, $user, $lng, 0);
        !isset($categories) ? $categories = new Categories($cfg, $lng, $db) : null;
        $hosts_view = get_listcat_hosts($cfg, $hosts, $user, $lng, $categories);
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
        $data['other_hosts']['cfg']['totals'] = $lng['L_SHOWED'] . ": $shown_hosts_count | {$lng['L_TOTAL']}: $hosts_totals_count | ";
        $data['other_hosts']['cfg']['onoff'] = $lng['L_ON'] . ": $host_on|{$lng['L_OFF']}: $host_off | ";
        $data['other_hosts']['data'] = $frontend->getTpl('hosts-min', $tdata);
    }
}

/* Set show/hide host-details */
if ($command === 'host-details' && is_numeric($command_value)) {
    $host_id = $command_value;
    $host_details = get_host_detail_view_data($db, $cfg, $hosts, $user, $lng, $host_id);
    if ($host_details) {
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
        unset($tdata['host_details']['ping_stats']);
        $data['host_details']['cfg']['place'] = "#left_container";
        $data['host_details']['data'] = $frontend->getTpl('host-details', $tdata);
    }
}

if ($command == 'saveNote' && !empty($command_value) && !empty($object_id)) {
    $set = ['content' => urldecode($command_value)];
    $where = ['id' => $object_id];

    $db->update('notes', $set, $where, 'LIMIT 1');
}

if ($command == 'setHighlight' && isset($command_value) && !empty($object_id)) {

    ($command_value == 0) ? $value = 0 : $value = 1;

    $hosts->update($object_id, ['highlight' => $value]);
}

/* Power ON/OFF  & Reboot */
if ($command == 'power_on' && !empty($command_value) && is_numeric($command_value)) {
    $host = $hosts->getHostById($command_value);

    if (!empty($host['mac'])) {
        sendWOL($host['mac']);
    } else {
        $log->warning("Host {$host['ip']} has not mac address");
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
if ($cfg['term_show_system'] && $cfg['log_to_db']) {
    
}

/* END ALWAYS */


/*  -   */
//$log->debug(print_r($data,true));
//print json_encode($data);
print json_encode($data, JSON_UNESCAPED_UNICODE);
