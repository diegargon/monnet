
<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
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

function common_head(array $cfg, Database $db, array $lng, User $user) {
    $page = [];

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

    /* Time Widget */
    require('modules/weather_widget/weather_widget.php');

    $page['web_main']['scriptlink'][] = 'https://code.jquery.com/jquery-2.2.4.min.js';
    $page['web_main']['scriptlink'][] = './scripts/common.js';
    $page['web_main']['scriptlink'][] = './modules/weather_widget/weather_widget.js';

    $page['weather_widget'] = weather_widget($cfg, $lng);
    $page['load_tpl'][] = [
        'file' => 'weather-widget',
        'place' => 'head_right',
    ];

    return $page;
}

function page_index(array $cfg, Database $db, array $lng, User $user) {
    $page = [];

    $page = common_head($cfg, $db, $lng, $user);

    $categories = new Categories($cfg, $db);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $post_data = page_index_post($db, $user, $categories, $lng);
        $page = array_merge($post_data, $page);
    }

    $items = new Items($cfg, $db);

    $page['page'] = 'index';
    $page['head_name'] = $cfg['web_title'];

    //Index scripts
    $page['web_main']['scriptlink'][] = './scripts/index.js';
    //Graph scripts
    $page['web_main']['scriptlink'][] = 'https://cdn.jsdelivr.net/npm/chart.js';
    $page['web_main']['scriptlink'][] = 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns';

    /* Include refresher script tpl */
    $page['web_main']['main_head_tpl'][] = 'refresher';

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
        'place' => 'head_left',
    ];

    /* AppLinks Bookmarks */
    if ($user->getPref('show_applinks_status')) {
        $applinks_bookmarks = format_items($user, $items->getCatAll(11));
        $page['bookmarks_category']['applinks'] = $applinks_bookmarks;
    }

    /* Bookmarks */

    if ($user->getPref('show_bookmarks_status')) {
        $bookmarks = format_items($user, $items->getCatAll(10));
        $page['bookmarks_category']['bookmarks'] = $bookmarks;
    }
    if ($user->getPref('show_applinks_status') || $user->getPref('show_bookmarks_status')) {
        $page['load_tpl'][] = [
            'file' => 'bookmarks',
            'place' => 'center_col',
        ];
    }
    $page['load_tpl'][] = [
        'file' => 'additem',
        'place' => 'center_col',
    ];

    $page['items_categories'] = $categories->getByType(2);

    return $page;
}

function page_index_post(Database $db, User $user, Categories $categories, array $lng) {
    $page_data = [];
    $profile_type = Filters::postString('profile_type');
    $show_bookmarks = Filters::postInt('show_bookmarks');
    $show_this_system = Filters::postInt('show_this_system');
    $show_applinks = Filters::postInt('show_applinks');
    $show_highlight_hosts = Filters::postInt('show_highlight_hosts');
    $show_other_hosts = Filters::postInt('show_rest_hosts');
    //add Item
    if (isset($_POST['addBookmarkForm'])) {
        $bookmarkName = Filters::postString('bookmarkName');
        $url_type = Filters::postInt('url_type');
        $urlip = Filters::postUrl('urlip');

        if (!$urlip) {
            $urlip = Filters::postIP('urlip');
        }

        $image_type = Filters::postString('image_type');
        if ($image_type == 'image_resource') {
            $field_img = Filters::postImgUrl('field_image');
        } else {
            $field_img = Filters::postStrict('field_image');
        }

        $weight = Filters::postInt('weight');
    }
    //
    if (isset($_POST['addBookmarkForm'])) {
        $page_data['show_add_bookmark'] = 1;

        if (empty($bookmarkName)) {
            $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_NAME']} {$lng['L_ERROR_EMPTY_INVALID']}";
        } else if (empty($urlip)) {
            $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_URLIP']} {$lng['L_ERROR_EMPTY_INVALID']}";
        } else if (empty($url_type)) {
            $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_TYPE']} {$lng['L_ERROR_EMPTY_INVALID']}";
        } else if (empty('weight')) {
            $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_WEIGHT']} {$lng['L_ERROR_EMPTY_INVALID']}";
        } else if (empty($image_type)) {
            $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_IMAGE_TYPE']} {$lng['L_ERROR_EMPTY_INVALID']}";
        }

        if ($image_type != 'favicon' && empty($field_img)) {
            $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_LINK']} {$lng['L_ERROR_EMPTY_INVALID']}";
        }

        $page_data['bookmarkName'] = $bookmarkName;
        $page_data['url_type'] = $url_type;
        $page_data['urlip'] = $urlip;
        $page_data['image_type'] = $image_type;
        $page_data['field_img'] = $field_img;
        $page_data['weight'] = $weight;
        if (empty($page_data['error_msg'])) {
            $conf = ['url' => $urlip, 'image_type' => $image_type, 'image_resource' => $field_img];
            $set = ['cat_id' => $url_type, 'title' => $bookmarkName, 'conf' => json_encode($conf), 'weight' => $weight];
            $db->insert('items', $set);
            $page_data['status_msg'] = 'OK';
        } else {
            $page_data['bookmarkName'] = $bookmarkName;
            $page_data['url_type'] = $url_type;
            $page_data['urlip'] = $urlip;
            $page_data['image_type'] = $image_type;
            $page_data['field_img'] = $field_img;
            $page_data['weight'] = $weight;
        }

        $cat_type = $categories->getTypeByID($url_type);
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

    return $page_data;
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
    $page['head_name'] = $cfg['web_title'];
    $page['web_main']['scriptlink'][] = 'https://code.jquery.com/jquery-2.2.4.min.js';
    $page['web_main']['scriptlink'][] = './scripts/background.js';

    $page['page'] = 'login';
    $page['tpl'] = 'login';
    $page['log_in'] = $lng['L_LOGIN'];

    if (isset($_COOKIE['username'])) {
        $page['username'] = htmlspecialchars($_COOKIE['username']);
    } else {
        $page['username'] = '';
    }

    $page['username_placeholder'] = $lng['L_USERNAME'];
    $page['password_placeholder'] = $lng['L_PASSWORD'];
    if (!empty($page['username'])) {
        $page['set_pass_focus'] = 1;
    } else {
        $page['set_username_focus'] = 1;
    }

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

function format_items(User $user, array $items_results) {

    $items = [];
    $theme = $user->getTheme();
    foreach ($items_results as $item) {
        $item_conf = json_decode($item['conf'], true);
        $item_img = '';
        if ($item_conf['image_type'] === 'favicon' && empty($item_conf['image_resource'])) {
            $item_img = $item_conf['url'] . '/favicon.ico';
        } else if ($item_conf['image_type'] === 'favicon') {
            $favicon_path = $item_conf['image_resource'];
            $item_img = $item_conf['url'] . '/' . $favicon_path;
        } elseif ($item_conf['image_type'] === 'url') {
            $item_img = $item_conf['image_resource'];
        } elseif ($item_conf['image_type'] === 'local_img') {
            $item_img = 'tpl/' . $theme . '/img/icons/' . $item_conf['image_resource'];
        }

        $item['img'] = $item_img;
        $items[] = array_merge($item, $item_conf);
    }

    return $items;
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
            $hosts_results[$khost]['online_image'] = 'tpl/' . $theme . '/img/green.png';
        } else {
            $hosts_results[$khost]['title_online'] = $lng['L_S_OFFLINE'];
            $hosts_results[$khost]['online_image'] = 'tpl/' . $theme . '/img/red.png';
        }

        if (!empty($vhost['os'])) {
            $hosts_results[$khost]['os'] = $cfg['os'][$vhost['os']]['name'];
            $hosts_results[$khost]['os_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['os'][$vhost['os']]['img'];
        } else {
            $hosts_results[$khost]['os'] = $cfg['os'][$vhost['os']]['name'];
            $hosts_results[$khost]['os_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['os'][$vhost['os']]['img'];
        }
        /* Demasiados iconos mejor para details

          if (!empty($vhost['os'])) {
          $hosts_results[$khost]['os'] = $cfg['system'][$vhost['os']]['name'];
          $hosts_results[$khost]['os_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['os'][$vhost['os']]['img'];
          } else {
          $hosts_results[$khost]['os'] = $cfg['system'][$vhost['system']]['name'];
          $hosts_results[$khost]['os_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['system'][$vhost['system']]['img'];
          }
          if (!empty($vhost['os_distribution'])) {
          $hosts_results[$khost]['os_distribution'] = $cfg['system'][$vhost['os_distribution']]['name'];
          $hosts_results[$khost]['os_distribution_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['os_distribution'][$vhost['os_distribution']]['img'];
          }
         */
        if (!empty($vhost['system'])) {
            $hosts_results[$khost]['system'] = $cfg['system'][$vhost['system']]['name'];
            $hosts_results[$khost]['system_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['system'][$vhost['system']]['img'];
        }


        //Warn icon
        if ($vhost['warn_port']) {
            $hosts_results[$khost]['warn_mark'] = 'tpl/' . $theme . '/img/error-mark.png';
            $hosts_results[$khost]['details'] .= $lng['L_PORT_DOWN'];
        }
    }

    return $hosts_results;
}

function get_host_detail_view_data(Database $db, array $cfg, Hosts $hosts, User $user, array $lng, $hid) {
    global $log;

    $host = $hosts->getHostById($hid);
    if (!$host) {
        return false;
    }

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
    $hosts_log_query = 'SELECT * 
            FROM hosts_logs
            WHERE host_id = ' . $host['id'] . '
            ORDER by date DESC limit 100;';
    $result = $db->query($hosts_log_query);
    $hosts_logs = $db->fetchAll($result);
    if (valid_array($hosts_logs)) {
        $host['host_logs'] = '';
        foreach ($hosts_logs as $host_log) {
            $host['host_logs'] .= '<div>[' . datetime_string_format($host_log['date'], $cfg['datetime_log_format']) . '] ' . $host_log['msg'] . '</div>';
        }
    }
    $theme = $user->getTheme();

    // Host Work
    $host['theme'] = $theme;
    if ($host['online']) {
        $host['title_online'] = $lng['L_S_ONLINE'];
        $host['online_image'] = 'tpl/' . $theme . '/img/green.png';
    } else {
        $host['title_online'] = $lng['L_S_OFFLINE'];
        $host['online_image'] = 'tpl/' . $theme . '/img/red.png';
    }

    if (!empty($host['os'])) {
        $host['os_name'] = $cfg['os'][$host['os']]['name'];
        $host['os_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['os'][$host['os']]['img'];
    }

    if (!empty($host['system'])) {
        $host['system_name'] = $cfg['system'][$host['system']]['name'];
        $host['system_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['system'][$host['system']]['img'];
    }

    if (!empty($host['os_distribution'])) {
        $host['os_distribution_name'] = $cfg['os_distribution'][$host['os_distribution']]['name'];
        $host['os_distribution_image'] = 'tpl/' . $theme . '/img/icons/' . $cfg['os_distribution'][$host['os_distribution']]['img'];
    }

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

    //Deploy
    $host['deploy'] = [];
    foreach ($cfg['deploys'] as $deploy) {
        if ($host['os_distribution'] == $deploy['os_distribution']) {
            $host['deploys'][] = $deploy;
        }
    }

    return $host;
}

function page_settings(array $cfg, Database $db, array $lng, User $user) {
    $page = [];

    $page = common_head($cfg, $db, $lng, $user);
    $page['page'] = 'index';
    $page['head_name'] = $cfg['web_title'];

    return $page;
}

function page_privacy(array $cfg, Database $db, array $lng, User $user) {
    $page = [];

    $page = common_head($cfg, $db, $lng, $user);
    $page['page'] = 'index';
    $page['head_name'] = $cfg['web_title'];
    $page['web_main']['scriptlink'][] = 'https://code.jquery.com/jquery-2.2.4.min.js';
    $page['web_main']['scriptlink'][] = './scripts/background.js';
    return $page;
}
