<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
header('Content-Type: application/json; charset=UTF-8');

/**
 * @var User|null $user An instance of User or null if not defined
 * @var AppContext|null $ctx An instance of Context or null if not defined
 * @var array<string,string> $lng
 * @var Database|null $db An instance of Database or null if not defined
 * @var array<int|string, mixed> $cfg
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
$tdata['theme'] = $cfg['theme'];

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

if ($user->getPref('show_termlog_status')) {
    $logs = [];
    $type_mark = '';

    $host_logs = Log::getLoghosts($cfg['term_max_lines']);

    if (!empty($host_logs)) {
        foreach ($host_logs as &$log) {
            $log['type_mark'] = '[H]';
        }
        $logs = $host_logs;
    }


    if ($cfg['term_show_system_logs'] && $cfg['log_to_db']) {
        $system_logs = Log::getSystemDBLogs($cfg['term_max_lines']);
        if (!empty($system_logs)) {
            foreach ($system_logs as &$system_log) {
                $system_log['type_mark'] = '[S]';
            }
            $logs = array_merge($logs, $system_logs);
        }
    }

    foreach ($logs as &$log) {
        $log['timestamp'] = strtotime($log['date']);
    }

    usort($logs, function ($a, $b) {
        return $b['timestamp'] <=> $a['timestamp'];
    });

    foreach ($logs as &$log) {
        unset($log['timestamp']);
    }

    //If we add systems logs probably we exceed the max
    if (valid_array($logs) && count($logs) > $cfg['term_max_lines']) {
        $term_logs = array_slice($logs, 0, $cfg['term_max_lines']);
    } else {
        $term_logs = $logs;
    }
    if (valid_array($term_logs)) {
        $log_lines = [];
        foreach ($term_logs as $term_log) {
            if (is_numeric($term_log['level'])) :
                $log_level = (int) $term_log['level'];
            else :
                continue;
            endif;

            $date = format_datetime_from_string($term_log['date'], $cfg['term_date_format']);
            $loglevelname = Log::getLogLevelName($log_level);
            $loglevelname = str_replace('LOG_', '', $loglevelname);
            $log_lines[] = $date . $term_log['type_mark'] . '[' . $loglevelname . ']' . $term_log['msg'] . '<br/>';
        }
        $data['term_logs']['cfg']['place'] = '#center-container';
        $data['term_logs']['data'] = $frontend->getTpl('term', ['term_logs' => $log_lines]);
    }
}

$data['misc']['totals'] = $lng['L_SHOWED'] . ": $show_hosts_count | {$lng['L_TOTAL']}: $hosts_totals_count";
$data['misc']['last_refresher'] = $lng['L_REFRESHED'] . ': ' . $user->getDateNow($cfg['datetime_format_min']);

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

//TODO2 Move to ncfg

$cli_last = 0;
$discovery_last = 0;

$results = $db->select('prefs', '*', ['uid' => 0]);

if ($results) :
    $system_prefs = $db->fetchAll($results);

    foreach ($system_prefs as $sys_pref) {
        if ($sys_pref['pref_name'] == 'cli_last_run') {
            if (empty($sys_pref['pref_value'])) {
                $cli_last = 'Never';
            } else {
                $cli_last = utc_to_tz(
                    $sys_pref['pref_value'],
                    $user->getTimezone(),
                    $cfg['datetime_format_min']
                );
            }
        } elseif ($sys_pref['pref_name'] == 'discovery_last_run') {
            if (empty($sys_pref['pref_value'])) {
                $discovery_last = 'Never';
            } else {
                $discovery_last = utc_to_tz(
                    $sys_pref['pref_value'],
                    $user->getTimezone(),
                    $cfg['datetime_format_min']
                );
            }
        }
    }
endif;

/* Usado para saber si hay alguien conectado */
$ncfg->set('refreshing', time());

$data['misc']['cli_last_run'] = 'CLI ' . strtolower($lng['L_UPDATED']) . ' ' . $cli_last;
$data['misc']['discovery_last_run'] = 'Discovery ' . strtolower($lng['L_UPDATED']) . ' ' . $discovery_last;

print json_encode($data, JSON_UNESCAPED_UNICODE);
