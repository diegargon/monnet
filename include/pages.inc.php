
<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function page_defaults($cfg, User $user) {
    $page = [];

    $_user = $user->getUser();

    empty($_user['theme']) ? $page['theme'] = $cfg['theme'] : $page['theme'] = $_user['theme'];
    empty($_user['lang']) ? $page['lang'] = $cfg['lang'] : $page['lang'] = $_user['lang'];
    empty($_user['charset']) ? $page['charset'] = $cfg['charset'] : $page['charset'] = $_user['charset'];
    $page['web_title'] = $cfg['web_title'];

    return $page;
}

function page_index($cfg, $db, $lng, $user) {

    $page = [];

    $page['page'] = 'index';
    $page['head_name'] = $cfg['web_title'];

    page_index_post($user);

    $results = $db->select('items', '*', ['type' => 'search_engine']);
    $search_engines = $db->fetchAll($results);

    foreach ($search_engines as $search_engine) {
        $conf = json_decode($search_engine['conf'], true);
        $page['search_engines'][] = [
            'url' => $conf['url'],
            'name' => $conf['name'],
        ];
    }
    //TODO: modules_load by page request

    /* Time Widget */
    require('modules/weather_widget/weather_widget.php');

    $page['web_main']['jsfile'][] = './modules/weather_widget/weather_widget.js';

    $page['weather_widget'] = weather_widget($cfg, $lng);
    $page['load_tpl'][] = [
        'file' => 'weather_widget',
        'place' => 'left_col',
    ];

    /* Controls */
    $show_bookmarks_status = $user->getPref('show_bookmarks_status');
    $show_services_status = $user->getPref('show_services_status');
    $show_this_system = $user->getPref('show_this_system_status');

    $show_bookmarks_status ? $page['controls']['show_bookmarks_status'] = 1 : $page['controls']['show_bookmarks_status'] = 0;
    $show_services_status ? $page['controls']['show_services_status'] = 1 : $page['controls']['show_services_status'] = 0;
    $show_this_system ? $page['controls']['show_this_system_status'] = 1 : $page['controls']['show_this_system_status'] = 0;

    $page['load_tpl'][] = [
        'file' => 'controls',
        'place' => 'left_col',
    ];

    /* bookmarks */

    $results = $db->select('items', '*', ['type' => 'bookmark']);
    $bookmarks_results = $db->fetchAll($results);

    /*
      usort($bookmarks, function ($a, $b) {
      return $a['weight'] <=> $b['weight'];
      });
     */
    if ($user->getPref('show_bookmarks_status')) {
        $bookmarks = [];
        foreach ($bookmarks_results as $bookmark) {
            $bookmark_conf = json_decode($bookmark['conf'], true);

            $theme = $user->getTheme();
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

        $page['load_tpl'][] = [
            'file' => 'bookmarks',
            'place' => 'center_col',
        ];

        $page['bookmarks'] = $bookmarks;
    }

    return $page;
}

function page_index_post($user) {
    $profile_type = Filters::postString('profile_type');
    $show_bookmarks = Filters::postInt('show_bookmarks');
    $show_this_system = Filters::postInt('show_this_system');
    $show_services = Filters::postInt('show_services');

    if ($profile_type !== false) {
        $user->setPref('profile_type', $profile_type);
    }
    if ($show_bookmarks !== false) {
        $user->setPref('show_bookmarks_status', $show_bookmarks);
    }
    if ($show_this_system !== false) {
        $user->setPref('show_this_system_status', $show_this_system);
    }
    if ($show_services !== false) {
        $user->setPref('show_services_status', $show_services);
    }
}

function page_login($cfg, $lng, $user) {


    $page = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $username = Filters::postUsername('username');
        $password = Filters::postPassword('password');
        if (!empty($username) && !empty($password)) {

            $userid = $user->checkUser($username, $password);
            if (!empty($userid) && $userid > 0) {
                $user->setUser($userid);
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
