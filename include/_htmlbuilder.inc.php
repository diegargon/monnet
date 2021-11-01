<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function build_bookmarks(string $category) {
    global $cfg;

    $bookmark_html = '';

    usort($cfg['bookmarks'], function ($a, $b) {
        return $a['weight'] <=> $b['weight'];
    });

    foreach ($cfg['bookmarks'] as $bookmark) {
        if (($category !== $bookmark['category']) || !empty($bookmark['disable'])) {
            continue;
        }
        if (!empty($cfg['profile_type']) && $cfg['profile_type'] !== 'all' && $bookmark['profile'] !== 'all') {
            if ($cfg['profile_type'] != $bookmark['profile']) {
                continue;
            }
        }

        $icon_bg = '';
        if (!empty($bookmark['icon_bg'])) {
            $icon_bg = 'background-color: ' . $bookmark['icon_bg'];
        }
        $bookmark_html .= '<div class="item-container">';
        $bookmark_html .= '<a href="' . $bookmark['url'] . '" rel="noreferrer" target="_blank" class="item" title="' . $bookmark['title'] . '">';
        $bookmark_html .= '<div class="item-thumb shadow1">';

        if ($bookmark['image_type'] === 'favicon' && empty($bookmark['image_resource'])) {
            $bookmark_html .= '<img rel="noreferrer" class="fab" src="' . $bookmark['url'] . '/favicon.ico" alt="' . $bookmark['title'] . '" style="' . $icon_bg . '" />';
        } else if ($bookmark['image_type'] === 'favicon') {
            $favicon_path = $bookmark['image_resource'];
            $bookmark_html .= '<img rel="noreferrer" class="fab" src="' . $bookmark['url'] . '/' . $favicon_path . '" alt="' . $bookmark['title'] . '" style="' . $icon_bg . '" />';
        } elseif ($bookmark['image_type'] === 'class') {
            $bookmark_html .= '<span class="' . $bookmark['image_resource'] . '"  style="background:rgba(0, 0, 0, 0.4);"></span>';
        } elseif ($bookmark['image_type'] === 'url') {
            $bookmark_html .= '<img rel="noreferrer" class="fab" src="' . $bookmark['image_resource'] . '" alt="' . $bookmark['title'] . '" style="' . $icon_bg . '"/>';
        } elseif ($bookmark['image_type'] === 'local_img') {
            $bookmark_html .= '<img class="fab" src="img/icons/' . $bookmark['image_resource'] . '" alt="' . $bookmark['title'] . '" style="' . $icon_bg . '"/>';
        }
        $bookmark_html .= '<div class="item-title">' . $bookmark['title'] . '</div>';
        $bookmark_html .= '</div></a></div>';
    }

    return $bookmark_html;
}

function profile_types() {
    global $cfg;

    $option = false;
    if (!empty($cfg['profile_type'])) {
        $option = $cfg['profile_type'];
    } else {
        $option = getCookieValue('profile_type');
    }
    $sel_all = $sel_work = $sel_home = '';

    if (empty($option)) {
        $sel_all = ' selected="" ';
    } else if ($option === 'work') {
        $sel_work = ' selected="" ';
    } else if ($option === 'home') {
        $sel_home = ' selected="" ';
    }

    $options = '<option value="all"' . $sel_all . '>All</option>';
    $options .= '<option value="work"' . $sel_work . '>Work</option>';
    $options .= '<option value="home"' . $sel_home . '>Home</option>';

    return $options;
}

function get_services() {

    global $cfg;

    set_services();
    if ($cfg['service_offline_top']) {
        foreach ($cfg['services'] as $kservice => $service) {
            if (empty($service['online'])) {
                $cfg['services'][$kservice]['weight'] = 1;
            }
        }
    }
    usort($cfg['services'], function ($a, $b) {
        return $a['weight'] - $b['weight'];
    });

    $service_html = '<div class="head_small_services">Services</div>';
    $service_html = '<div id="services" class="services">';
    foreach ($cfg['services'] as $service) {
        $service_html .= '<div class="service-container">';
        $service_html .= '<a href="" rel="noreferrer" target="_blank" class="service-item" title="' . $service['title'] . '">';

        $service_html .= '<div class="service-thumb shadow1">';
        if (!empty($service['online'])) {
            $service_html .= '<img class="service_state" src="img/services/green.png" alt="Online"/>';
        } else {
            $service_html .= '<img class="service_state" src="img/services/red.png" alt="Offline"/>';
        }
        $icon_bg = '';
        if (!empty($service['icon_bg'])) {
            $icon_bg = 'background-color: ' . $service['icon_bg'];
        }
        if (!empty($service['image_type'])) {

            if ($service['image_type'] === 'class') {
                $service_html .= '<span class="' . $service['image_resource'] . '" ></span>';
            } elseif ($service['image_type'] === 'url') {
                $service_html .= '<img rel="noreferrer" class="fab" src="' . $service['image_resource'] . '" alt="' . $service['title'] . '" style="' . $icon_bg . '"/>';
            } elseif ($service['image_type'] === 'local_img') {
                $service_html .= '<img class="fab" src="img/icons/' . $service['image_resource'] . '" alt="' . $service['title'] . '" style="' . $icon_bg . '"/>';
            }
        }
        $service_html .= '<div class="service-title">' . $service['title'] . '</div>';
        $service_html .= '</div></a></div>';
    }


    return $service_html;
}

function get_this_system() {
    $content = '';

    $load_avrgs = sys_getloadavg();
    $ncpus = get_ncpus();
    $load_now = round($load_avrgs[0] / $ncpus, 2);
    $load_avrg = round($load_avrgs[1] / $ncpus, 2);
    $meminfo = getSystemMem();
    $memory_usage = formatBytes($meminfo['used'], 2);
    $memory_total = formatBytes($meminfo['total'], 2);
    $content .= '<span class"cpu_load">Load: ' . $load_now . ' Avrg: ' . $load_avrg . ' Memory: ' . $memory_usage . '/' . $memory_total . '</span>';
    return $content;
}
