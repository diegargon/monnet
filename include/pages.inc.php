<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
function page_defaults(AppContext $ctx)
{
    $page = [];

    $user = $ctx->get('User');
    $cfg = $ctx->get('cfg');

    $_user = $user->getUser();

    empty($_user['theme']) ? $page['theme'] = $cfg['theme'] : $page['theme'] = $_user['theme'];
    empty($_user['lang']) ? $page['lang'] = $cfg['lang'] : $page['lang'] = $_user['lang'];
    empty($_user['charset']) ? $page['charset'] = $cfg['charset'] : $page['charset'] = $_user['charset'];
    $page['web_title'] = $cfg['web_title'];

    return $page;
}

function page_common_head(AppContext $ctx)
{
    $page = [];

    $db = $ctx->get('Mysql');
    //$user = $ctx->get('User');
    $cfg = $ctx->get('cfg');
    $lng = $ctx->get('lng');

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

    $page['web_main']['scriptlink'][] = './scripts/jquery-2.2.4.min.js';
    $page['web_main']['scriptlink'][] = './scripts/common.js';
    $page['web_main']['scriptlink'][] = './modules/weather_widget/weather_widget.js';

    $page['weather_widget'] = weather_widget($cfg, $lng);
    $page['load_tpl'][] = [
        'file' => 'weather-widget',
        'place' => 'head_right',
    ];

    /* Footer */
    $page['web_main']['main_footer_tpl'][] = 'footer';

    return $page;
}

function page_index(AppContext $ctx)
{
    $page = [];

    $user = $ctx->get('User');
    $cfg = $ctx->get('cfg');
    $categories = $ctx->get('Categories');
    $networks_list = $ctx->get('Networks')->getNetworks();
    $page = page_common_head($ctx);
    $networks_selected = 0;

    foreach ($networks_list as &$net) {
        $net_set = $user->getPref('network_select_' . $net['id']);
        if (($net_set) || $net_set === false) {
            $net['selected'] = 1;
            $networks_selected++;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        page_index_post($ctx);
    }

    $items = $ctx->get('Items');

    $page['page'] = 'index';
    $page['head_name'] = $cfg['web_title'];

    //Index scripts
    $page['web_main']['scriptlink'][] = './scripts/index.js';

    /* Graph scripts
     * https://cdn.jsdelivr.net/npm/chart.js
     * https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js
     */

    $page['web_main']['scriptlink'][] = './scripts/chart.js';
    $page['web_main']['scriptlink'][] = './scripts/chartjs-adapter-date-fns.bundle.min.js';

    /* Include refresher script tpl */
    $page['web_main']['main_head_tpl'][] = 'refresher';
    /* Include submmitter script tpl */
    $page['web_main']['main_head_tpl'][] = 'submitter';

    /* Top Buttons */
    $page['load_tpl'][] = [
        'file' => 'topbuttoms',
        'place' => 'head_left',
    ];

    /* Controls */
    $show_bookmarks_status = $user->getPref('show_bookmarks_status');
    $show_this_system = $user->getPref('show_this_system_status');
    $show_highlight_hosts_status = $user->getPref('show_highlight_hosts_status');
    $show_other_hosts_status = $user->getPref('show_other_hosts_status');
    $show_termlog_status = $user->getPref('show_termlog_status');
    $show_bookmarks_status ? $page['controls']['show_bookmarks_status'] = 1 :
            $page['controls']['show_bookmarks_status'] = 0;
    $show_highlight_hosts_status ? $page['controls']['show_highlight_hosts_status'] = 1 :
            $page['controls']['show_highlight_hosts_status'] = 0;
    $show_other_hosts_status ? $page['controls']['show_other_hosts_status'] = 1 :
            $page['controls']['show_other_hosts_status'] = 0;
    $show_this_system ? $page['controls']['show_this_system_status'] = 1 :
            $page['controls']['show_this_system_status'] = 0;
    $show_termlog_status ? $page['controls']['show_termlog_status'] = 1 :
            $page['controls']['show_termlog_status'] = 0;

    $page['load_tpl'][] = [
        'file' => 'controls',
        'place' => 'head_left',
    ];

    /* Bookmarks */

    if ($user->getPref('show_bookmarks_status')) {
        $bookmarks = format_items($user, $items->getByType('bookmarks'));
        $default_bookmarks_tab = $user->getPref('default_bookmarks_tab');
        if ($default_bookmarks_tab == null) {
            $default_bookmarks_tab = 0;
        }
        $page['bookmarks_default_tab'] = str_replace('bookmark_content_tab_', '', $default_bookmarks_tab);
        $page['bookmarks'] = $bookmarks;
        $bookmarks_head = $categories->prepareCats(2);

        $page['bookmarks_head'] = $bookmarks_head;
        $page['load_tpl'][] = [
            'file' => 'bookmarks',
            'place' => 'center_col',
        ];
    }
    /* Add Stdbox */
    $page['load_tpl'][] = [
        'file' => 'stdbox',
        'place' => 'center_col',
    ];
    /* Add Bookmark Item */
    $page['local_icons'] = getLocalIconsData('local_img/');
    $page['load_tpl'][] = [
        'file' => 'add-bookmark',
        'place' => 'center_col',
    ];

    /* Add Network Item */
    $page['load_tpl'][] = [
        'file' => 'add-network',
        'place' => 'center_col',
    ];

    /* Host Cat */
    $page['load_tpl'][] = [
        'file' => 'categories-host',
        'place' => 'left_col_pre',
    ];
    /* Host Footer */
    $page['load_tpl'][] = [
        'file' => 'footer-hosts',
        'place' => 'left_col_post',
    ];

    $page['hosts_categories'] = $user->getHostsCats();
    $page['networks'] = $networks_list;
    $page['networks_selected'] = $networks_selected; //to prevent unselect all

    /* Webs Categories */
    $page['web_categories'] = $categories->getByType(2);

    /* Network Categories */
    $page['network_categories'] = $categories->getByType(1);

    return $page;
}

function page_login(AppContext $ctx): array
{
    $page = [];

    //$db = $ctx->get('Mysql');
    $user = $ctx->get('User');
    $cfg = $ctx->get('cfg');
    $lng = $ctx->get('lng');

    if (
        !empty($_SERVER['REQUEST_METHOD']) &&
        $_SERVER['REQUEST_METHOD'] == 'POST'
    ) {
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
    $page['web_main']['scriptlink'][] = './scripts/jquery-2.2.4.min.js';
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

function page_logout(AppContext $ctx): void
{

    $cfg = $ctx->get('cfg');

    $_SESSION = [];
    session_destroy();

    setcookie('sid', '', time() - 3600, '/');
    setcookie('uid', '', time() - 3600, '/');
    (empty($cfg['rel_path'])) ? $cfg['rel_path'] = '/' : null;

    header("Location: {$cfg['rel_path']}index.php");
}

function page_settings(AppContext $ctx): array
{
    $page = [];

    $cfg = $ctx->get('cfg');

    $page = page_common_head($ctx);
    /* Top Buttons */
    $page['load_tpl'][] = [
        'file' => 'topbuttoms',
        'place' => 'head_left',
    ];
    $page['page'] = 'index';
    $page['head_name'] = $cfg['web_title'];

    return $page;
}

function page_privacy(AppContext $ctx): array
{
    $page = [];

    $cfg = $ctx->get('cfg');

    $page = page_common_head($ctx);
    /* Top Buttons */
    $page['load_tpl'][] = [
        'file' => 'topbuttoms',
        'place' => 'head_left',
    ];
    $page['page'] = 'index';
    $page['head_name'] = $cfg['web_title'];
    $page['web_main']['scriptlink'][] = './scripts/jquery-2.2.4.min.js';
    $page['web_main']['scriptlink'][] = './scripts/background.js';
    return $page;
}
