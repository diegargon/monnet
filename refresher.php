<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
header('Content-Type: application/json; charset=UTF-8');

/**
 * @var User|null $user An instance of User or null if not defined
 * @var AppContext|null $ctx An instance of Context or null if not defined
 * @var array<string,string> $lng
 * @var Database|null $db An instance of Database or null if not defined
 * @var Config $ncfg
 */
require_once 'include/common.inc.php';
require_once 'include/common-call.php';
require_once 'include/usermode.inc.php';
require_once 'include/refresher-call.php';

$tdata = [];
$hosts = $ctx->get('Hosts');

$data = [
    'conn' => 'success',
    'login' => 'fail',
    'response_msg' => '',
];

if ($user->getId() > 0) {
    $data['login'] = 'success';
} else {
    print(json_encode($data));
    exit();
}

$frontend = new Frontend($ctx);
$tdata['theme'] = $ncfg->get('theme');

$data['footer_dropdown'] = [];
$highlight_hosts_count = 0;
$hosts_totals_count = 0;
$show_hosts_count = 0;
$ansible_hosts = 0;
$ansible_hosts_off = 0;
$ansible_hosts_fail = 0;
$agent_hosts = 0;
$agent_hosts_off = 0;

/* Set show/hide highlight hosts */
if ($user->getPref('show_highlight_hosts_status')) {
    $hosts_view = get_hosts_view($ctx, 1);
    $highlight_hosts_count = 0;

    $highlight_hosts_count = count($hosts_view);
    $tdata = [];
    $tdata['hosts'] = $hosts_view;
    $tdata['container-id'] = 'highlight-hosts';
    $tdata['head-title'] = $lng['L_HIGHLIGHT_HOSTS'];
    $data['highlight_hosts']['data'] = $frontend->getTpl('hosts-min', $tdata);
    $data['highlight_hosts']['cfg']['place'] = '#host_place';
}

if ($user->getPref('show_other_hosts_status')) {
    $hosts_view = get_hosts_view($ctx);

    $show_hosts_count = count($hosts_view);
    $hosts_totals_count = $hosts->totals;
    $show_hosts_count = $show_hosts_count + $highlight_hosts_count;
    $total_hosts_on = $hosts->total_on;
    $total_hosts_off = $hosts->total_off;
    $tdata = [];
    $tdata['hosts'] = $hosts_view;
    $tdata['container-id'] = 'other-hosts';
    $tdata['head-title'] = $lng['L_OTHERS'];
    $data['other_hosts']['cfg']['place'] = '#host_place';
    $data['other_hosts']['data'] = $frontend->getTpl('hosts-min', $tdata);
}
//END show host and other hosts status

if ($user->getPref('show_termlog_status')) {
    $logs = [];
    $type_mark = '';

    // Get Host Relate Logs for termlog
    $logs_opt = [
        'limit' => $ncfg->get('term_max_lines'),
        'level' => $ncfg->get('term_hosts_log_level'),
        'ack' => 1,
    ];
    $host_logs = Log::getLogsHosts($logs_opt);

    // Formatting
    if (!empty($host_logs)) :
        foreach ($host_logs as &$log) :
            $host = $hosts->getHostById($log['host_id']);
            $log['type_mark'] = '[H]';
            if (!empty($host['display_name'])) :
                $log['display_name'] = '[' . $host['display_name'] . ']';
            elseif (!empty($host['ip'])) :
                $log['display_name'] = '[' . $host['ip'] . ']';
            else :
                $log['display_name'] = '[' . $log['host_id'] . ']';
            endif;
        endforeach;
        $logs = $host_logs;
    endif;

    // Get System Logs for termlog if we log into de database
    if ($ncfg->get('term_show_system_logs') && $ncfg->get('system_log_to_db')) :
        $system_logs = Log::getSystemDBLogs($ncfg->get('term_max_lines'));
        // Formatting
        if (!empty($system_logs)) :
            foreach ($system_logs as &$system_log) :
                $system_log['type_mark'] = '[S]';
                $system_log['display_name'] = '';
            endforeach;
            $logs = array_merge($logs, $system_logs);
        endif;
    endif;

    foreach ($logs as &$log) :
        if (!empty($log['date'])) {
            $log['timestamp'] = strtotime($log['date']);
        }
    endforeach;

    usort($logs, function ($a, $b) {
        if (!is_array($a) || !is_array($b)) {
            return 0;
        }
        if (!isset($a['timestamp'], $b['timestamp'])) {
            return 0;
        }

        return $b['timestamp'] <=> $a['timestamp'];
    });

    foreach ($logs as &$log) :
        unset($log['timestamp']);
    endforeach;

    //If we add systems logs probably we exceed the max
    if (valid_array($logs) && count($logs) > $ncfg->get('term_max_lines')) {
        $term_logs = array_slice($logs, 0, $ncfg->get('term_max_lines'));
    } else {
        $term_logs = $logs;
    }
    if (valid_array($term_logs)) {
        $log_lines = [];
        foreach ($term_logs as $term_log) {
            if (isset($term_log['level']) && is_numeric($term_log['level'])) :
                $log_level = (int) $term_log['level'];
            else :
                continue;
            endif;

            $date = format_datetime_from_string($term_log['date'], $ncfg->get('term_date_format'));
            $loglevelname = LogLevel::getName($log_level);
            $loglevelname = str_replace('LOG_', '', $loglevelname);
            $loglevelname = substr($loglevelname, 0, 4);
            if ($log_level <= 2) :
                $loglevelname = '<span class="color-red">' . $loglevelname . '</span>';
            elseif ($log_level === 3) :
                $loglevelname = '<span class="color-orange">' . $loglevelname . '</span>';
            elseif ($log_level === 4) :
                $loglevelname = '<span class="color-yellow">' . $loglevelname . '</span>';
            endif;
            $log_lines[] = $date . $term_log['type_mark'] .
                '[' . $loglevelname . ']' . $term_log['display_name'] . $term_log['msg'] .
                '<br/>';
        }
        $data['term_logs']['cfg']['place'] = '#center-container';
        $data['term_logs']['data'] = $frontend->getTpl('term', ['term_logs' => $log_lines]);
    }
}
// show_termlog_status

$data['misc']['totals'] = $lng['L_SHOWED'] . ": $show_hosts_count | {$lng['L_TOTAL']}: $hosts_totals_count";


$memory_usage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
$start_time = $_SERVER["REQUEST_TIME_FLOAT"];
$execution_time = round(microtime(true) - $start_time, 2);
$load = sys_getloadavg();
$cpu_usage = round($load[0], 2);
$data['misc']['last_refresher'] = $lng['L_REFRESHED'] . ': ' . $user->getDateNow($ncfg->get('datetime_format_min'));
$data['misc']['last_refresher'] .= " ($memory_usage|$execution_time|$cpu_usage)";

$data['footer_dropdown'][] = [
    'value' => $total_hosts_on ?? 0,
    'desc' => $lng['L_HOSTS_ON'],
    'number-color' => 'blue'
];
$data['footer_dropdown'][] = [
    'value' => $total_hosts_off ?? 0,
    'desc' => $lng['L_HOSTS_OFF'],
    'number-color' => 'red'
];

if ($hosts->alerts) :
    $data['footer_dropdown'][] = [
        'value' => $hosts->alerts,
        'report_type' => 'alerts',
        'desc' => $lng['L_ALERTS'],
        'number-color' => 'red'
    ];
endif;

if ($hosts->warns) :
    $data['footer_dropdown'][] = [
        'value' => $hosts->warns,
        'report_type' => 'warns',
        'desc' => $lng['L_WARNS'],
        'number-color' => 'orange'
    ];
endif;

if ($ncfg->get('ansible')) :
    $ansible_hosts_on = $ansible_hosts - $ansible_hosts_off;
    if ($hosts->ansible_hosts) :
        $data['footer_dropdown'][] = [
            'value' => $hosts->ansible_hosts,
            'report_type' => 'ansible_hosts',
            'desc' => $lng['L_ANSIBLE_HOSTS'],
            'number-color' => 'blue'
        ];
    endif;
    if ($hosts->ansible_hosts_off) :
        $data['footer_dropdown'][] = [
            'value' => $hosts->ansible_hosts_off,
            'report_type' => 'ansible_hosts_off',
            'desc' => $lng['L_ANSIBLE_HOSTS_OFF'],
            'number-color' => 'red'
        ];
    endif;
    if ($hosts->ansible_hosts_fail) :
        $data['footer_dropdown'][] = [
            'value' => $hosts->ansible_hosts_fail,
            'report_type' => 'ansible_hosts_fail',
            'desc' => $lng['L_ANSIBLE_HOSTS_FAIL'],
            'number-color' => 'red'
        ];
    endif;
endif;

if ($hosts->agents) :
    $data['footer_dropdown'][] = [
        'value' => $hosts->agents,
        'report_type' => 'agents_hosts',
        'desc' => $lng['L_AGENT_HOSTS'],
        'number-color' => 'blue'
    ];
endif;
if ($hosts->agents_off) :
    $data['footer_dropdown'][] = [
        'value' => $hosts->agents_off,
        'report_type' => 'agents_hosts_off',
        'desc' => $lng['L_AGENT_HOSTS_OFF'],
        'number-color' => 'red'
    ];
endif;

if ($hosts->agents_missing_pings) :
    $data['footer_dropdown'][] = [
        'value' => $hosts->agents_missing_pings,
        'report_type' => 'agents_hosts_missing_pings',
        'desc' => $lng['L_AGENT_MISSING_PINGS'],
        'number-color' => 'red'
    ];
endif;

/*
 *  Update top host-bar
 */


// Host Categories
/*
$hosts_categories = $user->getHostsCats();
foreach ($hosts_categories as $key => $host_cat) {
    if (!$hosts->catHaveHosts($host_cat['id'])) {
        unset($hosts_categories[$key]);
    }
}
$tdata['hosts_categories'] = $hosts_categories;

// Host Networks
$networks_list = $ctx->get('Networks')->getNetworks();
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

//Load Tpl
$data['categories_host']['data'] = $frontend->getTpl('hosts-bar', $tdata);
$data['categories_host']['cfg']['place'] = '#left-container';
*/

/*
 *  Host Down Bar
 */

$cli_last_run = 'Never';
if ($ncfg->get('cli_last_run')) {
    $cli_last_run = $ncfg->get('cli_last_run');
    $cli_last_run = utc_to_tz($cli_last_run, $user->getTimezone(), $ncfg->get('datetime_format_min'));
    $cli_last_run .= $ncfg->get('cli_last_run_metrics');
}

$discovery_last_run = 'Never';
if ($ncfg->get('discovery_last_run')) {
    $discovery_last_run = $ncfg->get('discovery_last_run');
    $discovery_last_run = utc_to_tz($discovery_last_run, $user->getTimezone(), $ncfg->get('datetime_format_min'));
    $discovery_last_run .= $ncfg->get('discovery_last_run_metrics');
}

/* Usado para saber si hay alguien conectado */
$ncfg->set('refreshing', time());

$data['misc']['cli_last_run'] = 'CLI ' . strtolower($lng['L_UPDATED']) . ' ' . $cli_last_run;
$data['misc']['discovery_last_run'] = 'Discovery ' . strtolower($lng['L_UPDATED']) . ' ' . $discovery_last_run;

print json_encode($data, JSON_UNESCAPED_UNICODE);
