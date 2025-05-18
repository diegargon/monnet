<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

use App\Core\AppContext;
use App\Core\DBManager;

use App\Services\UserService;
use App\Services\NetworksService;
use App\Services\Filter;
use App\Utils\NetUtils;

/**
 *
 *
 * @param AppContext $ctx
 * @return array<string,string>
 */
function page_defaults(AppContext $ctx): array
{
    $ncfg = $ctx->get('Config');
    $page = [];

    $userService = new UserService($ctx);
    $_user = $userService->getCurrentUser();

    //$_user = $user->getCurrentUser();

    if (empty($_user['theme'])) {
        $page['theme'] = $ncfg->get('theme');
    } else {
        $page['theme'] = $_user['theme'];
    }
    if (empty($_user['lang'])) {
        $page['lang'] = $ncfg->get('lang');
    } else {
        $page['lang'] = $_user['lang'];
    }
    if (empty($_user['page_charset'])) {
        $page['page_charset'] = $ncfg->get('default_charset');
    } else {
        $page['page_charset'] = $_user['charset'];
    }

    $page['web_title'] = $ncfg->get('web_title');

    return $page;
}

/**
 *
 * @param AppContext $ctx
 * @return array<string, array<mixed>>
 */
function page_common_head(AppContext $ctx): array
{
    $page = [];

    $db = $ctx->get('Mysql');
    //$user = $ctx->get('User');
    $lng = $ctx->get('lng');
    $ncfg = $ctx->get('Config');

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
        'file' => 'main-center-box',
        'place' => 'head-center',
    ];

    /* Time Widget */
    require('modules/weather_widget/weather_widget.php');

    $page['web_main']['scriptlink'][] = './scripts/jquery-2.2.4.min.js';
    $page['web_main']['scriptlink'][] = './scripts/common.js';


    $weather = weather_widget($ncfg, $lng);
    if (!empty($weather)) {
        $page['web_main']['scriptlink'][] = './modules/weather_widget/weather_widget.js';
        $page['weather_widget'] = $weather;
        $page['load_tpl'][] = [
            'file' => 'weather-widget',
            'place' => 'head-right',
        ];
    }

    /* Footer */
    $page['web_main']['main_footer_tpl'][] = 'footer';

    return $page;
}

/**
 *
 * @param AppContext $ctx
 * @return array<string,string>
 */
function page_index(AppContext $ctx): array
{
    $page = [];

    $user = $ctx->get('User');
    $ncfg = $ctx->get('Config');
    $categories = $ctx->get('Categories');
    $networks  = new NetworksService($ctx);
    $networks_list = $networks->getNetworks();
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
    $page['head_name'] = $ncfg->get('web_title');

    //Index scripts
    $page['web_main']['scriptlink'][] = './scripts/index.js';

    /* Graph scripts
     * https://cdn.jsdelivr.net/npm/chart.js
     * https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js
     */

    $page['web_main']['scriptlink'][] = './scripts/chart.js';
    $page['web_main']['scriptlink'][] = './scripts/chartjs-adapter-date-fns.bundle.min.js';

    /* Include refresher script tpl */
    $page['web_main']['main_head_tpl'][] = 'refresher-js';
    /* Include submmitter script tpl */
    $page['web_main']['main_head_tpl'][] = 'submitter-js';

    /* Top Buttons */
    $page['load_tpl'][] = [
        'file' => 'topbuttoms',
        'place' => 'head-left',
    ];

    /* Controls */
    $show_bookmarks_status = $user->getPref('show_bookmarks_status');
    $show_highlight_hosts_status = $user->getPref('show_highlight_hosts_status');
    $show_other_hosts_status = $user->getPref('show_other_hosts_status');
    $show_termlog_status = $user->getPref('show_termlog_status');
    $show_bookmarks_status ? $page['controls']['show_bookmarks_status'] = 1 :
            $page['controls']['show_bookmarks_status'] = 0;
    $show_highlight_hosts_status ? $page['controls']['show_highlight_hosts_status'] = 1 :
            $page['controls']['show_highlight_hosts_status'] = 0;
    $show_other_hosts_status ? $page['controls']['show_other_hosts_status'] = 1 :
            $page['controls']['show_other_hosts_status'] = 0;
    $show_termlog_status ? $page['controls']['show_termlog_status'] = 1 :
            $page['controls']['show_termlog_status'] = 0;

    /* Left-Right Controls Templeates */
    $page['username'] = $user->getUsername();
    $page['load_tpl'][] = [
        'file' => 'controls-left',
        'place' => 'head-left',
    ];
    $page['load_tpl'][] = [
        'file' => 'controls-right',
        'place' => 'head-right',
        'weight' => 4
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
            'place' => 'right_col',
        ];
    }
    /* Add Stdbox */
    $page['load_tpl'][] = [
        'file' => 'stdbox',
        'place' => 'right_col',
    ];

    /* Host Cat */
    $page['load_tpl'][] = [
        'file' => 'hosts-bar',
        'place' => 'left_col_pre',
    ];
    /* Host Footer */
    $page['load_tpl'][] = [
        'file' => 'footer-hosts',
        'place' => 'left_col',
    ];

    $page['hosts_categories'] = $user->getHostsCats();
    $page['networks'] = $networks_list;
    $page['networks_selected'] = $networks_selected; //to prevent unselect all

    /* Webs Categories */
    $page['web_categories'] = $categories->getByType(2);

    /* Network Categories */
    //??$page['network_categories'] = $categories->getByType(1);

    return $page;
}

/**
 *
 * @param AppContext $ctx
 * @return array<string,mixed>
 */
function _page_login(AppContext $ctx): array
{
    $page = [];

    //$db = $ctx->get('Mysql');
    $user = $ctx->get('User');
    $ncfg = $ctx->get('Config');
    $lng = $ctx->get('lng');

    if (
        !empty($_SERVER['REQUEST_METHOD']) &&
        $_SERVER['REQUEST_METHOD'] == 'POST'
    ) {
        $username = Filter::postUsername('username');
        $password = Filter::postPassword('password');
        if (!empty($username) && !empty($password)) {
            $userid = $user->checkUser($username, $password);
            if (!empty($userid) && $userid > 0) {
                $user->setUser($userid);
                if (empty($ncfg->get('rel_path'))) {
                    $ncfg->set('rel_path', '/');
                }
                header("Location: {$ncfg->get('rel_path')} ");

                exit();
            }
        }
    }
    $page['head_name'] = $ncfg->get('web_title');
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

/**
 *
 * @param AppContext $ctx
 * @return array<string,mixed>
 */
function page_login(AppContext $ctx): array
{
    $page = [];

    //$db = $ctx->get('Mysql');
    $userService = new UserService($ctx);

    $ncfg = $ctx->get('Config');
    $lng = $ctx->get('lng');

    if (
        !empty($_SERVER['REQUEST_METHOD']) &&
        $_SERVER['REQUEST_METHOD'] == 'POST'
    ) {
        $username = Filter::postUsername('username');
        $password = Filter::postPassword('password');
        if (!empty($username) && !empty($password)) {
            $user = $userService->login($username, $password);
            if (!empty($user['id']) && $user['id'] > 0) {
                if (empty($ncfg->get('rel_path'))) {
                    $ncfg->set('rel_path', '/');
                }
                header("Location: {$ncfg->get('rel_path')} ");

                exit();
            }
        }
    }
    $page['head_name'] = $ncfg->get('web_title');
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

/**
 *
 * @param AppContext $ctx
 * @return void
 */

function page_logout(AppContext $ctx): void
{

    $ncfg = $ctx->get('Config');

    $_SESSION = [];
    session_destroy();

    setcookie('sid', '', time() - 3600, '/');
    setcookie('uid', '', time() - 3600, '/');
    (empty($ncfg->get('rel_path'))) ? $ncfg->set('rel_path', '/') : null;

    header("Location: {$ncfg->get('rel_path')}index.php");
}

/**
 *
 * @param AppContext $ctx
 * @return array<string,string>
 */
function page_settings(AppContext $ctx): array
{
    $page = [];

    $ncfg = $ctx->get('Config');
    $config_all = $ctx->get('Config')->getAllEditable();
    $groupedConfig = [];
    foreach ($config_all as $config) {
        $ccat = $config['ccat'];
        //if ( ($json = isJson($config['cvalue']))):
        //    $config['cvalue'] = $json;
        //endif;
        $groupedConfig[$ccat][] = $config;
    }

    $page = page_common_head($ctx);
    $page['groupedConfig'] = $groupedConfig;
    /* Top Buttons */
    $page['load_tpl'][] = [
        'file' => 'topbuttoms',
        'place' => 'head-left',
    ];
    $page['page'] = 'index';
    $page['head_name'] = $ncfg->get('web_title');
    $page['web_main']['scriptlink'][] = './scripts/settings.js';

    $page['load_tpl'][] = [
        'file' => 'settings',
        'place' => 'left_col',
    ];

    return $page;
}

/**
 *
 * @param AppContext $ctx
 * @return array<string,string>
 */
function page_privacy(AppContext $ctx): array
{
    $page = [];

    $ncfg = $ctx->get('Config');

    $page = page_common_head($ctx);
    /* Top Buttons */
    $page['load_tpl'][] = [
        'file' => 'topbuttoms',
        'place' => 'head-left',
    ];

    $page['page'] = 'index';
    $page['head_name'] = $ncfg->get('web_title');
    $page['web_main']['scriptlink'][] = './scripts/jquery-2.2.4.min.js';
    $page['web_main']['scriptlink'][] = './scripts/background.js';

    return $page;
}

/**
 * TODO: User/Users management
 * @param AppContext $ctx
 * @return array<string,string>
 */
function page_user(AppContext $ctx): array
{
    $ncfg = $ctx->get('Config');
    $page = [];
    $userService = new UserService($ctx);
    $user = $userService->getCurrentUser();

    $page = page_common_head($ctx);

    /* Top Buttons */
    $page['load_tpl'][] = [
        'file' => 'topbuttoms',
        'place' => 'head-left',
    ];

    $page['page'] = 'index';
    $page['head_name'] = $ncfg->get('web_title');
    //$page['web_main']['scriptlink'][] = './scripts/jquery-2.2.4.min.js';
    //$page['web_main']['scriptlink'][] = './scripts/background.js';

    $page['load_tpl'][] = [
        'file' => 'user',
        'place' => 'left_col_pre',
    ];
    $page['load_tpl'][] = [
        'file' => 'user-mgmt',
        'place' => 'right_col',
    ];
    return $page;
}

function format_items(User $user, array $items_results): array
{
    $items = [];
    $theme = $user->getTheme();
    foreach ($items_results as $item) {
        $item_conf = json_decode($item['conf'], true);
        $item_img = '';
        if ($item_conf['image_type'] === 'favicon' && empty($item_conf['image_resource'])) {
            $item_img = $item_conf['url'] . '/favicon.ico';
            //$item_img = NetUtils::cachedImg($user, $item['id'], $item_img);
            //$item_img = NetUtils::cachedImg($user, $item['id'], $item_img);
        } elseif ($item_conf['image_type'] === 'favicon') {
            $favicon_path = $item_conf['image_resource'];
            $parse_item_img = NetUtils::baseUrl($item_conf['url']);
            if ($parse_item_img !== false) :
                $item_img = $parse_item_img . '/' . $favicon_path;
                //$item_img = NetUtils::cachedImg($user, $item['id'], $item_img);
            endif;
        } elseif ($item_conf['image_type'] === 'url' && !empty($item_conf['image_resource'])) {
            $item_img = $item_conf['image_resource'];
            //$item_img = NetUtils::cachedImg($user, $item['id'], $item_img);
        } elseif ($item_conf['image_type'] === 'local_img') {
            $item_img = '/bookmarks_icons/' . $item_conf['image_resource'];
        } else {
            $item_img = 'bookmarks_icons/www.png';
        }

        $item['img'] = $item_img;
        $items[] = array_merge($item, $item_conf);
    }

    return $items;
}

function page_index_post(AppContext $ctx): bool
{
    $user = $ctx->get('User');

    $profile_type = Filter::postString('profile_type');
    $show_bookmarks = Filter::postInt('show_bookmarks');
    $show_highlight_hosts = Filter::postInt('show_highlight_hosts');
    $show_other_hosts = Filter::postInt('show_rest_hosts');
    $show_termlog = Filter::postInt('show_termlog');
    //add Item

    if ($profile_type !== null) {
        $user->setPref('profile_type', $profile_type);
    }
    if ($show_bookmarks !== null) {
        $user->setPref('show_bookmarks_status', $show_bookmarks);
    }
    if ($show_highlight_hosts !== null) {
        $user->setPref('show_highlight_hosts_status', $show_highlight_hosts);
    }
    if ($show_other_hosts !== null) {
        $user->setPref('show_other_hosts_status', $show_other_hosts);
    }
    if ($show_termlog !== null) {
        $user->setPref('show_termlog_status', $show_termlog);
    }

    return true;
}