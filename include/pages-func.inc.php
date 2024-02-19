<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function format_items(User $user, array $items_results) {
    global $log;

    $items = [];
    $theme = $user->getTheme();
    foreach ($items_results as $item) {
        //global $log; $log->debug('Formatting item '. $item['title'] );
        $item_conf = json_decode($item['conf'], true);
        $item_img = '';
        if ($item_conf['image_type'] === 'favicon' && empty($item_conf['image_resource'])) {
            $item_img = $item_conf['url'] . '/favicon.ico';
            $item_img = cached_img($log, $user, $item['id'], $item_img);
        } else if ($item_conf['image_type'] === 'favicon') {
            $favicon_path = $item_conf['image_resource'];
            $item_img = base_url($item_conf['url']) . '/' . $favicon_path;
            $item_img = cached_img($log, $user, $item['id'], $item_img);
        } elseif ($item_conf['image_type'] === 'url') {
            $item_img = $item_conf['image_resource'];
            $item_img = cached_img($log, $user, $item['id'], $item_img);
        } elseif ($item_conf['image_type'] === 'local_img') {
            $item_img = 'tpl/' . $theme . '/img/icons/' . $item_conf['image_resource'];
        }

        $item['img'] = $item_img;
        $items[] = array_merge($item, $item_conf);
    }

    return $items;
}

//TODO this and next rewrite
function get_listcat_hosts(array $cfg, Hosts $hosts, User $user, array $lng, Categories $cats) {
    $hostscat = [];

    $cats_on = $cats->getOnByType(1);
    if ($cats_on === false) {
        return false;
    }
    //Get Host for each ON category
    foreach ($cats_on as $cat) {
        $hosts_cat = $hosts->getHostsByCat($cat['id']);
        if (valid_array($hosts_cat)) {
            $hostscat = array_merge($hostscat, $hosts_cat);
        }
    }

    if (!valid_array($hostscat)) {
        return false;
    }

    $theme = $user->getTheme();

    foreach ($hostscat as $khost => $host) {
        //Discard highlight host for other hosts
        if ($user->getPref('show_highlight_hosts_status') && $host['highlight']) {
            unset($hostscat[$khost]);
        }
        //Discard hidden networks
        $host_network_pref = 'network_select_' . $host['network'];
        if ($user->getPref($host_network_pref) == 0) {
            unset($hostscat[$khost]);
        }
    }

    foreach ($hostscat as $khost => $vhost) {
        $hostscat[$khost]['theme'] = $theme;
        $hostscat[$khost]['details'] = $lng['L_IP'] . ': ' . $vhost['ip'] . "\n";
        if (empty($vhost['title'])) {
            if (!empty($vhost['hostname'])) {
                $hostscat[$khost]['title'] = explode('.', $vhost['hostname'])[0];
            } else {
                $hostscat[$khost]['title'] = $vhost['ip'];
            }
        } else {
            if (!empty($vhost['hostname'])) {
                $hostscat[$khost]['details'] .= $lng['L_HOSTNAME'] . ': ' . $vhost['hostname'] . "\n";
            }
        }
        if ($vhost['online']) {
            $hostscat[$khost]['title_online'] = $lng['L_S_ONLINE'];
            $hostscat[$khost]['online_image'] = 'tpl/' . $theme . '/img/green2.png';
        } else {
            $hostscat[$khost]['title_online'] = $lng['L_S_OFFLINE'];
            $hostscat[$khost]['online_image'] = 'tpl/' . $theme . '/img/red2.png';
        }

        $manufacture = get_manufacture_data($cfg, $vhost['manufacture']);
        $os = get_os_data($cfg, $vhost['os']);
        $system_type = get_system_type_data($cfg, $vhost['system_type']);

        $hostscat[$khost]['manufacture_name'] = $manufacture['name'];
        $hostscat[$khost]['manufacture_image'] = 'tpl/' . $theme . '/img/icons/' . $manufacture['img'];

        $hostscat[$khost]['os_name'] = $os['name'];
        $hostscat[$khost]['os_image'] = 'tpl/' . $theme . '/img/icons/' . $os['img'];

        $hostscat[$khost]['system_type_name'] = $system_type['name'];
        $hostscat[$khost]['system_type_image'] = 'tpl/' . $theme . '/img/icons/' . $system_type['img'];

        //Warn icon
        if ($vhost['warn_port']) {
            $hostscat[$khost]['warn_mark'] = 'tpl/' . $theme . '/img/error-mark.png';
            $hostscat[$khost]['details'] .= $lng['L_PORT_DOWN'];
        }
    }

    return $hostscat;
}

function get_hosts_view_data(array $cfg, Hosts $hosts, User $user, array $lng, int $highlight = 0) {

    $hosts_results = $hosts->getHighLight($highlight);

    if (!valid_array($hosts_results)) {
        return false;
    }
    $theme = $user->getTheme();

    foreach ($hosts_results as $khost => $vhost) {
        $hosts_results[$khost]['theme'] = $theme;
        $hosts_results[$khost]['details'] = $lng['L_IP'] . ': ' . $vhost['ip'] . "\n";
        if (empty($vhost['title'])) {
            if (!empty($vhost['hostname'])) {
                $hosts_results[$khost]['title'] = explode('.', $vhost['hostname'])[0];
            } else {
                $hosts_results[$khost]['title'] = $vhost['ip'];
            }
        } else {
            if (!empty($vhost['hostname'])) {
                $hosts_results[$khost]['details'] .= $lng['L_HOSTNAME'] . ': ' . $vhost['hostname'] . "\n";
            }
        }
        if ($vhost['online']) {
            $hosts_results[$khost]['title_online'] = $lng['L_S_ONLINE'];
            $hosts_results[$khost]['online_image'] = 'tpl/' . $theme . '/img/green2.png';
        } else {
            $hosts_results[$khost]['title_online'] = $lng['L_S_OFFLINE'];
            $hosts_results[$khost]['online_image'] = 'tpl/' . $theme . '/img/red2.png';
        }


        $manufacture = get_manufacture_data($cfg, $vhost['manufacture']);
        $os = get_os_data($cfg, $vhost['os']);
        $system_type = get_system_type_data($cfg, $vhost['system_type']);

        $hosts_results[$khost]['manufacture_name'] = $manufacture['name'];
        $hosts_results[$khost]['manufacture_image'] = 'tpl/' . $theme . '/img/icons/' . $manufacture['img'];

        $hosts_results[$khost]['os_name'] = $os['name'];
        $hosts_results[$khost]['os_image'] = 'tpl/' . $theme . '/img/icons/' . $os['img'];

        $hosts_results[$khost]['system_type_name'] = $system_type['name'];
        $hosts_results[$khost]['system_type_image'] = 'tpl/' . $theme . '/img/icons/' . $system_type['img'];

        //Warn icon
        if ($vhost['warn_port']) {
            $hosts_results[$khost]['warn_mark'] = 'tpl/' . $theme . '/img/error-mark.png';
            if (!empty(['warn_msg'])) {
                $hosts_results[$khost]['details'] .= $vhost['warn_msg'];
            } else {
                $hosts_results[$khost]['details'] .= $lng['L_PORT_DOWN'];
            }
        }
    }

    return $hosts_results;
}

function get_host_detail_view_data(Database $db, array $cfg, Hosts $hosts, User $user, array $lng, $hid) {
    global $log;

    $host = $hosts->getHostById($hid);
    $categories = new Categories($cfg, $lng, $db);

    if (!valid_array($host) || !valid_array($categories)) {
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
            $ping['date'] = utc_to_user_timezone($ping['date'], $cfg['timezone']);
        }
        $host['ping_stats'] = $ping_stats;
    }

    //HOST LOGS
    $host['host_logs'] = $log->getLoghost($host['id'], $cfg['term_max_lines']);

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

    $manufacture = get_manufacture_data($cfg, $host['manufacture']);
    $os = get_os_data($cfg, $host['os']);

    $system_type = get_system_type_data($cfg, $host['system_type']);

    $host['manufacture_name'] = $manufacture['name'];
    $host['manufacture_image'] = 'tpl/' . $theme . '/img/icons/' . $manufacture['img'];

    $host['os_name'] = $os['name'];
    $host['os_image'] = 'tpl/' . $theme . '/img/icons/' . $os['img'];

    $host['system_type_name'] = $system_type['name'];
    $host['system_type_image'] = 'tpl/' . $theme . '/img/icons/' . $system_type['img'];

    if (!empty($host['last_seen'])) {
        $host['f_last_seen'] = utc_to_user_timezone($host['last_seen'], $cfg['timezone'], $cfg['datetime_format']);
    }
    if (!empty($host['last_check'])) {
        $host['f_last_check'] = utc_to_user_timezone($host['last_check'], $cfg['timezone'], $cfg['datetime_format']);
    }
    $host['formated_creation_date'] = utc_to_user_timezone($host['created'], $cfg['timezone'], $cfg['datetime_format']);

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

function get_manufacture_data(array $cfg, int $id) {
    foreach ($cfg['manufacture'] as $manufacture) {
        if ($manufacture['id'] == $id) {
            return $manufacture;
        }
    }
    return false;
}

function get_os_data(array $cfg, int $id) {
    foreach ($cfg['os'] as $os) {
        if ($os['id'] == $id) {
            return $os;
        }
    }
    return false;
}

function get_system_type_data(array $cfg, int $id) {
    foreach ($cfg['system_type'] as $system_type) {
        if ($system_type['id'] == $id) {
            return $system_type;
        }
    }
    return false;
}
