<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function get_hosts_view(AppContext $ctx, int $highlight = 0): array|bool
{
    $cfg = $ctx->get('cfg');
    $hosts = $ctx->get('Hosts');
    $user = $ctx->get('User');
    $lng = $ctx->get('lng');
    $hosts_view = [];

    if ($highlight) {
        $hosts_view = $hosts->getHighLight($highlight);
    } else {
        $user_cats_state = $user->getHostsCatState();

        if (!valid_array($user_cats_state)) {
            return [];
        }
        foreach ($user_cats_state as $cat_id => $cat_state) {
            if ($cat_state == 1) {
                $hosts_cat = $hosts->getHostsByCat($cat_id);
                if (valid_array($hosts_cat)) {
                    $hosts_view = array_merge($hosts_view, $hosts_cat);
                }
            }
        }
    }
    //Return empty to avoid keep hosts after last turn off last cat
    if (!valid_array($hosts_view)) {
        return [];
    }

    $theme = $user->getTheme();

    if (!$highlight) {
        foreach ($hosts_view as $key => $host) {
            //Discard highlight host for other hosts
            if ($user->getPref('show_highlight_hosts_status') && $host['highlight']) {
                unset($hosts_view[$key]);
            }

            //Filter unslect network
            $pref_value = $user->getPref('network_select_' . $host['network']);
            if ($pref_value === '0') { //pref_value is string
                unset($hosts_view[$key]);
            }

            //DUP TO DELETE
            //Discard hidden networks
            /*
              $host_network_pref = 'network_select_' . $host['network'];
              if ($user->getPref($host_network_pref) === 0) {
              unset($hosts_view[$key]);
              }
             *
             */
        }
    }

    $date_now = new DateTime('now', new DateTimeZone('UTC'));

    //Formatting
    foreach ($hosts_view as $key => $vhost) {
        $hosts_view[$key]['theme'] = $theme;
        $hosts_view[$key]['details'] = $lng['L_IP'] . ': ' . $vhost['ip'] . "\n";
        if (empty($vhost['title'])) {
            if (!empty($vhost['hostname'])) {
                $hosts_view[$key]['title'] = explode('.', $vhost['hostname'])[0];
            } else {
                $hosts_view[$key]['title'] = $vhost['ip'];
            }
        } else {
            if (!empty($vhost['hostname'])) {
                $hosts_view[$key]['details'] .= $lng['L_HOSTNAME'] . ': ' . $vhost['hostname'] . "\n";
            }
        }
        if ($vhost['online']) {
            $hosts_view[$key]['title_online'] = $lng['L_S_ONLINE'];
            $hosts_view[$key]['online_image'] = 'tpl/' . $theme . '/img/green2.png';
        } else {
            $hosts_view[$key]['title_online'] = $lng['L_S_OFFLINE'];
            $hosts_view[$key]['online_image'] = 'tpl/' . $theme . '/img/red2.png';
        }

        if (!empty($vhost['manufacture'])) {
            $manufacture = get_manufacture_data($cfg, $vhost['manufacture']);
            $hosts_view[$key]['manufacture_name'] = $manufacture['name'];
            $hosts_view[$key]['manufacture_image'] = 'tpl/' . $theme . '/img/icons/' . $manufacture['img'];
        }
        if (!empty($vhost['os'])) {
            $os = get_os_data($cfg, $vhost['os']);
            $hosts_view[$key]['os_name'] = $os['name'];
            $hosts_view[$key]['os_image'] = 'tpl/' . $theme . '/img/icons/' . $os['img'];
        }
        if (!empty($vhost['system_type'])) {
            $system_type = get_system_type_data($cfg, $vhost['system_type']);
            $hosts_view[$key]['system_type_name'] = $system_type['name'];
            $hosts_view[$key]['system_type_image'] = 'tpl/' . $theme . '/img/icons/' . $system_type['img'];
        }

        $hosts_view[$key]['glow'] = '';

        // Glow

        $change_time = new DateTime($vhost['online_change'], new DateTimeZone('UTC'));
        $diff = $date_now->diff($change_time);
        $minutes_diff = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

        //$id = $vhost['id'];
        if ($minutes_diff > 0 && ($minutes_diff <= $cfg['refresher_time'])) {
            if ($vhost['online']) {
                $hosts_view[$key]['glow'] = 'host-glow-on';
            } else {
                $hosts_view[$key]['glow'] = 'host-glow-off';
            }
        }
        // /glow
        //Warn icon
        if ($vhost['warn_port']) {
            $hosts_view[$key]['warn_mark'] = 'tpl/' . $theme . '/img/error-mark.png';
            $hosts_view[$key]['details'] .= $lng['L_PORT_DOWN'];
        }
    }

    //Fix why not work for all hosts?
    order($hosts_view, 'display_name');

    return $hosts_view;
}

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
    $host['host_logs'] = Log::getLoghost($host['id'], $cfg['term_max_lines']);

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

function get_manufacture_data(array $cfg, int $id): array|bool
{
    foreach ($cfg['manufacture'] as $manufacture) {
        if ($manufacture['id'] == $id) {
            return $manufacture;
        }
    }
    return false;
}

function get_os_data(array $cfg, int $id): array|bool
{
    foreach ($cfg['os'] as $os) {
        if ($os['id'] == $id) {
            return $os;
        }
    }
    return false;
}

function get_system_type_data(array $cfg, int $id): array|bool
{
    foreach ($cfg['system_type'] as $system_type) {
        if ($system_type['id'] == $id) {
            return $system_type;
        }
    }
    return false;
}
