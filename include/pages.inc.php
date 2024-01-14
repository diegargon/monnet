
<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2023 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function page_defaults(array $cfg, User $user) {
    $page = [];

    $_user = $user->getUser();

    empty($_user['theme']) ? $page['theme'] = $cfg['theme'] : $page['theme'] = $_user['theme'];
    empty($_user['lang']) ? $page['lang'] = $cfg['lang'] : $page['lang'] = $_user['lang'];
    empty($_user['charset']) ? $page['charset'] = $cfg['charset'] : $page['charset'] = $_user['charset'];
    $page['web_title'] = $cfg['web_title'];

    return $page;
}

function page_index(array $cfg, Database $db, array $lng, User $user) {
    $page = [];

    $page['page'] = 'index';
    $page['head_name'] = $cfg['web_title'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        page_index_post($user);
    }

    $results = $db->select('items', '*', ['type' => 'search_engine']);
    $search_engines = $db->fetchAll($results);

    foreach ($search_engines as $search_engine) {
        $conf = json_decode($search_engine['conf'], true);
        $page['search_engines'][] = [
            'url' => $conf['url'],
            'name' => $conf['name'],
        ];
    }
    $page['load_tpl'][] = [
        'file' => 'search-box',
        'place' => 'head_center',
    ];

    //TODO: modules_load by page request

    /* Time Widget */
    require('modules/weather_widget/weather_widget.php');

    $page['web_main']['jsfile'][] = './modules/weather_widget/weather_widget.js';
    /* Include refresher script tpl */
    $page['web_main']['main_head_tpl'][] = 'refresher';

    $page['weather_widget'] = weather_widget($cfg, $lng);
    $page['load_tpl'][] = [
        'file' => 'weather-widget',
        'place' => 'head_left',
    ];

    /* Controls */
    $show_bookmarks_status = $user->getPref('show_bookmarks_status');
    $show_applinks_status = $user->getPref('show_applinks_status');
    $show_this_system = $user->getPref('show_this_system_status');
    $show_highlight_hosts_status = $user->getPref('show_highlight_hosts_status');
    $show_other_hosts_status = $user->getPref('show_other_hosts_status');
    $show_bookmarks_status ? $page['controls']['show_bookmarks_status'] = 1 : $page['controls']['show_bookmarks_status'] = 0;
    $show_applinks_status ? $page['controls']['show_applinks_status'] = 1 : $page['controls']['show_applinks_status'] = 0;
    $show_highlight_hosts_status ? $page['controls']['show_highlight_hosts_status'] = 1 : $page['controls']['show_highlight_hosts_status'] = 0;
    $show_other_hosts_status ? $page['controls']['show_other_hosts_status'] = 1 : $page['controls']['show_other_hosts_status'] = 0;
    $show_this_system ? $page['controls']['show_this_system_status'] = 1 : $page['controls']['show_this_system_status'] = 0;

    $page['load_tpl'][] = [
        'file' => 'controls',
        'place' => 'head_right',
    ];

    /* AppLinks Bookmarks */
    if ($user->getPref('show_applinks_status')) {
        $applinks_bookmarks = get_bookmarks($db, $user, 'applinks');
        $page['bookmarks_category']['applinks'] = $applinks_bookmarks;
    }

    /* Bookmarks */

    if ($user->getPref('show_bookmarks_status')) {
        $bookmarks = get_bookmarks($db, $user, 'bookmarks');
        $page['bookmarks_category']['bookmarks'] = $bookmarks;
    }
    if ($user->getPref('show_applinks_status') || $user->getPref('show_bookmarks_status')) {
        $page['load_tpl'][] = [
            'file' => 'bookmarks',
            'place' => 'center_col',
        ];
    }

    return $page;
}

function page_index_post(User $user) {
    $profile_type = Filters::postString('profile_type');
    $show_bookmarks = Filters::postInt('show_bookmarks');
    $show_this_system = Filters::postInt('show_this_system');
    $show_applinks = Filters::postInt('show_applinks');
    $show_highlight_hosts = Filters::postInt('show_highlight_hosts');
    $show_other_hosts = Filters::postInt('show_rest_hosts');
    $close_host_details = Filters::postInt('close_host_details_x'); //img click _x _y

    if (!empty($close_host_details)) {
        $user->setPref('host_details', 0);
    }
    if ($profile_type !== false) {
        $user->setPref('profile_type', $profile_type);
    }
    if ($show_bookmarks !== false) {
        $user->setPref('show_bookmarks_status', $show_bookmarks);
    }
    if ($show_this_system !== false) {
        $user->setPref('show_this_system_status', $show_this_system);
    }
    if ($show_applinks !== false) {
        $user->setPref('show_applinks_status', $show_applinks);
    }
    if ($show_highlight_hosts !== false) {
        $user->setPref('show_highlight_hosts_status', $show_highlight_hosts);
    }
    if ($show_other_hosts !== false) {
        $user->setPref('show_other_hosts_status', $show_other_hosts);
    }
}

function page_login(array $cfg, array $lng, User $user) {
    $page = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $username = Filters::postUsername('username');
        $password = Filters::postPassword('password');
        if (!empty($username) && !empty($password)) {

            $userid = $user->checkUser($username, $password);
            if (!empty($userid) && $userid > 0) {
                $user->setUser($userid);
                if (empty($cfg['rel_path'])) {
                    $cfg['rel_path'] = '/';
                }
                header("Location: {$cfg['rel_path']} ");

                exit();
            }
        }
    }

    $page['page'] = 'login';
    $page['tpl'] = 'login';
    $page['log_in'] = $lng['L_LOGIN'];
    $page['username_placeholder'] = $lng['L_USERNAME'];
    $page['password_placeholder'] = $lng['L_PASSWORD'];

    return $page;
}

function page_logout(array $cfg, array $lng, User $user) {

    session_destroy();
    $_SESSION['uid'] = '';
    $_SESSION['gid'] = '';
    
    setcookie('sid', '', time() - 3600, '/');
    setcookie('uid', '', time() - 3600, '/');
    (empty($cfg['rel_path'])) ? $cfg['rel_path'] = '/' : null;
    

    header("Location: {$cfg['rel_path']}index.php");
}

function get_bookmarks(Database $db, User $user, string $category) {

    $results = $db->select('items', '*', ['type' => $category], 'ORDER BY weight');
    $bookmarks_results = $db->fetchAll($results);

    $bookmarks = [];
    $theme = $user->getTheme();
    foreach ($bookmarks_results as $bookmark) {
        $bookmark_conf = json_decode($bookmark['conf'], true);

        if ($bookmark_conf['image_type'] === 'favicon' && empty($bookmark_conf['image_resource'])) {
            $bookmark_img = $bookmark_conf['url'] . '/favicon.ico';
        } else if ($bookmark_conf['image_type'] === 'favicon') {
            $favicon_path = $bookmark_conf['image_resource'];
            $bookmark_img = $bookmark_conf['url'] . '/' . $favicon_path;
        } elseif ($bookmark_conf['image_type'] === 'url') {
            $bookmark_img = $bookmark_conf['image_resource'];
        } elseif ($bookmark_conf['image_type'] === 'local_img') {
            $bookmark_img = 'tpl/' . $theme . '/img/icons/' . $bookmark_conf['image_resource'];
        }
        $bookmark['img'] = $bookmark_img;
        $bookmarks[] = array_merge($bookmark, $bookmark_conf);
    }

    return $bookmarks;
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
        if (!empty($vhost['img_ico'])) {
            $hosts_results[$khost]['img_ico'] = 'tpl/' . $theme . '/img/icons/' . $vhost['img_ico'];
        }
        if ($vhost['online']) {
            $hosts_results[$khost]['alt_online'] = $lng['L_S_ONLINE'];
            $hosts_results[$khost]['online_image'] = 'tpl/' . $theme . '/img/green.png';
        } else {
            $hosts_results[$khost]['alt_online'] = $lng['L_S_OFFLINE'];
            $hosts_results[$khost]['online_image'] = 'tpl/' . $theme . '/img/red.png';
        }
        if (!empty($vhost['system'])) {
            $hosts_results[$khost]['system_name'] = $cfg['system'][$vhost['system']]['name'];
            $hosts_results[$khost]['system_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['system'][$vhost['system']]['img'];
            $hosts_results[$khost]['details'] .= $lng['L_SYSTEM'] . ': ' . ucfirst($hosts_results[$khost]['system_name']) . "\n";
        }
        if (!empty($vhost['distributor'])) {
            $hosts_results[$khost]['details'] .= $lng['L_DISTRIBUTION'] . ': ' . ucfirst($cfg['os_distributions'][$vhost['distributor']]) . "\n";
            $hosts_results[$khost]['distributor'] = $cfg['os_distributions'][$vhost['distributor']];
        }
        if (!empty($vhost['codename'])) {
            $hosts_results[$khost]['details'] .= $lng['L_CODENAME'] . ': ' . ucfirst($vhost['codename']) . "\n";
        }

        //Warn icon
        if ($vhost['warn_port']) {
            $hosts_results[$khost]['warn_mark'] = 'tpl/' . $theme . '/img/error-mark.png';
            $hosts_results[$khost]['details'] .= $lng['L_PORT_DOWN'];
        }
    }

    return $hosts_results;
}

function get_host_detail_view_data(array $cfg, Hosts $hosts, User $user, array $lng, $hid) {

    $host = $hosts->getHostById($hid);
    if (!$host) {
        return false;
    }

    $theme = $user->getTheme();

    // Host Work
    $host['theme'] = $theme;
    if (!empty($host['img_ico'])) {
        $host['img_ico'] = 'tpl/' . $theme . '/img/icons/' . $host['img_ico'];
    }
    if ($host['online']) {
        $host['alt_online'] = $lng['L_S_ONLINE'];
        $host['online_image'] = 'tpl/' . $theme . '/img/green.png';
    } else {
        $host['alt_online'] = $lng['L_S_OFFLINE'];
        $host['online_image'] = 'tpl/' . $theme . '/img/red.png';
    }
    if (!empty($host['system'])) {
        $host['system_name'] = $cfg['system'][$host['system']]['name'];
        $host['system_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['system'][$host['system']]['img'];
    }
    if (!empty($host['distributor'])) {
        $host['distributor_text'] = $cfg['os_distributions'][$host['distributor']];
    }

    if (!empty($host['last_seen']) && is_numeric($host['last_seen'])) {
        $host['f_last_seen'] = timestamp_to_date($host['last_seen']);
    }
    if (!empty($host['last_check']) && is_numeric($host['last_check'])) {
        $host['f_last_check'] = timestamp_to_date($host['last_check']);
    }
    $host['formated_creation_date'] = formated_date($host['created']);

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

    //Deploy
    $host['deploy'] = [];
    foreach ($cfg['deploys'] as $deploy) {
        if ($host['distributor'] == $deploy['os_distribution']) {
            $host['deploys'][] = $deploy;
        }
    }

    return $host;
}

function top_bar() {
    
}
