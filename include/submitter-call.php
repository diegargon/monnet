<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
/**
 *
 * @param AppContext $ctx
 * @param int $hid
 * @return array<string, mixed>|null
 */
function get_host_detail_view_data(AppContext $ctx, int $hid): ?array
{
    $hosts = $ctx->get('Hosts');
    $db = $ctx->get('Mysql');
    $cfg = $ctx->get('cfg');
    $user = $ctx->get('User');
    $lng = $ctx->get('lng');
    $host = $hosts->getHostById($hid);

    $categories = $ctx->get('Categories');

    if (!valid_array($host)) {
        return null;
    }

    $host['hosts_categories'] = $categories->getByType(1);

    $theme = $user->getTheme();

    // Host Work
    $host['theme'] = $theme;
    if ($host['online']) :
        $host['title_online'] = $lng['L_S_ONLINE'];
        $host['online_image'] = 'tpl/' . $theme . '/img/green2.png';
    else :
        $host['title_online'] = $lng['L_S_OFFLINE'];
        $host['online_image'] = 'tpl/' . $theme . '/img/red2.png';
    endif;

    if (!empty($host['manufacture'])) :
        $manufacture = get_manufacture_data($cfg, $host['manufacture']);
        if ($manufacture) :
            $host['manufacture_name'] = $manufacture['name'];
            $host['manufacture_image'] = $manufacture['manufacture_image'];
        endif;
    endif;

    if (!empty($host['os'])) :
        $os = get_os_data($cfg, $host['os']);
        if ($os) :
            $host['os_name'] = $os['name'];
            $host['os_image'] = $os['os_image'];
        endif;
    endif;

    if (!empty($host['system_type'])) :
        $system_type = get_system_type_data($cfg, $host['system_type']);
        if ($system_type) :
            $host['system_type_name'] = $system_type['name'];
            $host['system_type_image'] = $system_type['system_type_image'];
        endif;
    endif;

    if (!empty($host['last_seen'])) :
        $host['f_last_seen'] = utc_to_tz($host['last_seen'], $cfg['timezone'], $cfg['datetime_format']);
    endif;

    if (!empty($host['last_check'])) :
        $host['f_last_check'] = utc_to_tz($host['last_check'], $cfg['timezone'], $cfg['datetime_format']);
    endif;

    $host['formated_creation_date'] = utc_to_tz($host['created'], $cfg['timezone'], $cfg['datetime_format']);

    if ($host['online'] && !empty($host['latency'])) :
        $host['latency_ms'] = micro_to_ms($host['latency']) . 'ms';
    endif;

    //formatted ports
    $host['ports_formated'] = '';
    if (valid_array($host['ports'])) {
        $total_elements = count($host['ports']) - 1;

        foreach ($host['ports'] as $index => $port) :
            $host['ports_formated'] .= $port['n'] . '/';
            $host['ports_formated'] .= ($port['port_type'] === 1) ? 'tcp' : 'udp';
            $host['ports_formated'] .= '/' . $port['name'];
            $host['ports_formated'] .= ($index === $total_elements) ? '' : ',';
        endforeach;
    }

    if (!empty($host['load_avg'])) :
        $loadavg = unserialize($host['load_avg']);
        (!empty($host['ncpu'])) ? $ncpu = (float)$host['ncpu'] : (float)$ncpu = 1;

        //$m1 = (float)$loadavg['1min'];
        //$m5 = (float)$loadavg['5min'];
        //$m15 = (float)$loadavg['15min'];
        $m1 = floatToPercentage((float)$loadavg['1min'], 0.0, $ncpu);
        $m5 = floatToPercentage((float)$loadavg['5min'], 0.0, $ncpu);
        $m15 = floatToPercentage((float)$loadavg['15min'], 0.0, $ncpu);

        $host['load_avg'] = [
            ['value' => round($m1, 1), 'legend' => $lng['L_LOAD'] . ' 1m', 'min' => 0, 'max' => 100],
            ['value' => round($m5, 1), 'legend' => $lng['L_LOAD'] . ' 5m', 'min' => 0, 'max' => 100],
            ['value' => round($m15, 1), 'legend' => $lng['L_LOAD'] . ' 15m', 'min' => 0, 'max' => 100],
        ];
    endif;

    if (!empty($host['mem_info'])) :
        $mem_info = unserialize($host['mem_info']);
        $total = $mem_info['total'];
        $used = $mem_info['used'];
        $legend = 'Mem: ' . $used . '(' . $mem_info['percent'] . '%)';
        $host['mem_info'] =  ['value' => $used, 'legend' => $legend, 'min' => 0, 'max' => $total];
    endif;

    if (!empty($host['disks_info'])) :
        $disksinfo = unserialize($host['disks_info']);
        $host['disks_info'] = [];

        foreach ($disksinfo as $disk) :
            $disk_percent = round($disk['percent']);
            $legend =  "($disk_percent%) : {$disk['mountpoint']} : {$disk['device']} {$disk['fstype']}";
            $host['disks_info'][] = [
                'value' => $disk['used'],
                'legend' => $legend,
                'min' => 0,
                'max' => $disk['total']
            ];
        endforeach;
    endif;

    if (!empty($host['uptime']) && is_numeric($host['uptime'])) :
        $uptime_ary = secondsToDHMS($host['uptime']);
        $host['uptime'] = "{$uptime_ary['days']} {$lng['L_DAYS']} {$uptime_ary['hours']} "
            . " {$lng['L_HOURS']} {$uptime_ary['minutes']} {$lng['L_MINUTES']}";
    endif;

    if (!empty($host['agent_installed']) && !empty($host['agent_last_contact'])) :
        $host['agent_last_contact'] = format_timestamp(
                $host['agent_last_contact'],
                $cfg['timezone'], $cfg['datetime_format']
            );
    endif;

    return $host;
}

/**
 *
 * @param AppContext $ctx
 * @param array<int, array<string, string>> $logs
 * @param string $nl
 * @return array<string>
 */
function format_host_logs(AppContext $ctx, array $logs, string $nl = '<br/>'): array
{
    $cfg = $ctx->get('cfg');

    $log_lines = [];
    foreach ($logs as $term_log) :
        if (is_numeric($term_log['level'])) :
            $date = format_datetime_from_string($term_log['date'], $cfg['term_date_format']);
            $loglevelname = Log::getLogLevelName((int) $term_log['level']);
            $loglevelname = str_replace('LOG_', '', $loglevelname);
            $log_lines[] = $date . '[' . $loglevelname . ']' . $term_log['msg'] . $nl;
        endif;
    endforeach;

    return $log_lines;
}

/**
 * TODO: To Hosts?
 * @param AppContext $ctx
 * @param int $host_id
 * @return array<int|string, mixed>
 */
function get_host_metrics(AppContext $ctx, int $host_id): array
{
    $cfg = $ctx->get('cfg');
    $db = $ctx->get('Mysql');

    $ping_states_query = 'SELECT *
        FROM stats
        WHERE host_id = ' . $host_id . ' AND
        type = 1
        AND date >= NOW() - INTERVAL 1 DAY
        ORDER BY date DESC;';

    $result = $db->query($ping_states_query);
    $ping_stats = $db->fetchAll($result);
    if (valid_array($ping_stats)) :
        foreach ($ping_stats as &$ping) :
            $ping['date'] = utc_to_tz($ping['date'], $cfg['timezone']);
        endforeach;

        return $ping_stats;
    endif;

    return [];
}
