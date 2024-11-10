<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
function get_host_detail_view_data(AppContext $ctx, $hid): array|bool
{
    $hosts = $ctx->get('Hosts');
    $db = $ctx->get('Mysql');
    $cfg = $ctx->get('cfg');
    $user = $ctx->get('User');
    $lng = $ctx->get('lng');
    $host = $hosts->getHostById($hid);

    $categories = $ctx->get('Categories');

    if (!valid_array($host)) {
        return false;
    }

    $host['hosts_categories'] = $categories->getByType(1);

    $ping_states_query = 'SELECT *
        FROM stats
        WHERE host_id = ' . $host['id'] . ' AND
        type = 1
        AND date >= NOW() - INTERVAL 1 DAY
        ORDER BY date DESC;';

    $result = $db->query($ping_states_query);
    $ping_stats = $db->fetchAll($result);
    if (valid_array($ping_stats)) {
        foreach ($ping_stats as &$ping) {
            $ping['date'] = utc_to_user_tz($ping['date'], $cfg['timezone']);
        }
        $host['ping_stats'] = $ping_stats;
    }

    //HOST LOGS
    $host['host_logs'] = Log::getLoghost($host['id'], ['max_lines' => $cfg['term_max_lines']]);

    $theme = $user->getTheme();

    // Host Work
    $host['theme'] = $theme;
    if ($host['online']) {
        $host['title_online'] = $lng['L_S_ONLINE'];
        $host['online_image'] = 'tpl/' . $theme . '/img/green2.png';
    } else {
        $host['title_online'] = $lng['L_S_OFFLINE'];
        $host['online_image'] = 'tpl/' . $theme . '/img/red2.png';
    }

    if (!empty($host['manufacture'])) {
        $manufacture = get_manufacture_data($cfg, $host['manufacture']);
        $host['manufacture_name'] = $manufacture['name'];
        $host['manufacture_image'] = 'tpl/' . $theme . '/img/icons/' . $manufacture['img'];
    }
    if (!empty($host['os'])) {
        $os = get_os_data($cfg, $host['os']);
        $host['os_name'] = $os['name'];
        $host['os_image'] = 'tpl/' . $theme . '/img/icons/' . $os['img'];
    }
    if (!empty($host['system_type'])) {
        $system_type = get_system_type_data($cfg, $host['system_type']);
        $host['system_type_name'] = $system_type['name'];
        $host['system_type_image'] = 'tpl/' . $theme . '/img/icons/' . $system_type['img'];
    }

    if (!empty($host['last_seen'])) {
        $host['f_last_seen'] = utc_to_user_tz($host['last_seen'], $cfg['timezone'], $cfg['datetime_format']);
    }
    if (!empty($host['last_check'])) {
        $host['f_last_check'] = utc_to_user_tz($host['last_check'], $cfg['timezone'], $cfg['datetime_format']);
    }
    $host['formated_creation_date'] = utc_to_user_tz($host['created'], $cfg['timezone'], $cfg['datetime_format']);

    if ($host['online'] && !empty($host['latency'])) {
        $host['latency_ms'] = micro_to_ms($host['latency']) . 'ms';
    }
    if (!empty($host['access_results'])) {
        $host_details = json_decode($host['access_results'], true);

        if (!empty($host_details) && is_array($host_details)) {
            foreach ($host_details as $k_host_details => $v_host_details) {
                if (!empty($v_host_details)) {
                    $host[$k_host_details] = $v_host_details;
                }
            }
        }
        unset($host['access_results']);
        //var_dump($host);
    }
    //formatted ports
    $host['ports_formated'] = '';
    if (valid_array($host['ports'])) {
        $total_elements = count($host['ports']) - 1;

        foreach ($host['ports'] as $index => $port) {
            $host['ports_formated'] .= $port['n'] . '/';
            $host['ports_formated'] .= ($port['port_type'] === 1) ? 'tcp' : 'udp';
            $host['ports_formated'] .= '/' . $port['name'];
            $host['ports_formated'] .= ($index === $total_elements) ? '' : ',';
        }
    }
    // Load Average Cacl
    if (
        !empty($host['loadavg'][1]) &&
        !empty($host['ncpu']) &&
        is_numeric($host['loadavg'][1])
    ) {
        $host['f_loadavg'] = 100 * $host['loadavg'][1];
        $host['f_maxload'] = 100 * $host['ncpu'];
    }

    /*
      $host['deploy'] = [];
      foreach ($cfg['deploys'] as $deploy) {
      if ($host['os_distribution'] == $deploy['os_distribution']) {
      $host['deploys'][] = $deploy;
      }
      }
     */
    return $host;
}

function format_host_logs($ctx, $logs, $nl = '<br/>'): array
{
    $cfg = $ctx->get('cfg');

    $log_lines = [];
    foreach ($logs as $term_log) {
        $date = datetime_string_format($term_log['date'], $cfg['term_date_format']);
        $loglevelname = Log::getLogLevelName($term_log['level']);
        $loglevelname = str_replace('LOG_', '', $loglevelname);
        $log_lines[] = $date . '[' . $loglevelname . ']' . $term_log['msg'] . $nl;
    }

    return $log_lines;
}
