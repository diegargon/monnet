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
 * @param int $highlight
 * @return array<string, mixed>
 */
function get_hosts_view(AppContext $ctx, int $highlight = 0): array
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
                $hosts_view[$key]['glow'] = 'host-glow-green';
            } else {
                $hosts_view[$key]['glow'] = 'host-glow-red';
            }
        }

        /*
         * Alert/Warn
         * Show msg on tooltip (details)
         */

        if ($vhost['alert'] && empty($vhost['disable_alarms'])) {
            $hosts_view[$key]['alert_mark'] = 'tpl/' . $theme . '/img/alert-mark.png';
            $hosts_view[$key]['details'] .= $vhost['warn_msg'];
        }
        if ($vhost['warn'] || $vhost['warn_port'] && empty($vhost['disable_alarms'])) {
            $hosts_view[$key]['warn_mark'] = 'tpl/' . $theme . '/img/warn-mark.png';
            if ($vhost['warn']) {
                $hosts_view[$key]['warn_msg'] .= $vhost['warn_msg'];
            } else {
                $hosts_view[$key]['details'] .= $lng['L_PORT_DOWN'];
            }
        }
    }

    //Fix why not work for all hosts?
    order($hosts_view, 'display_name');

    return $hosts_view;
}
