<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/
namespace App\Services;

use App\Core\AppContext;
use App\Core\ConfigService;

class PageIndexService
{
    /**
     *
     * @param AppContext $ctx
     * @return array<string, mixed>
     */
    public static function getIndex(AppContext $ctx): array
    {
        $page = [];
        $user = $ctx->get('User');
        $ncfg = $ctx->get(ConfigService::class);

        $categories = $ctx->get('Categories');
        $networks  = new NetworksService($ctx);
        $networks_list = $networks->getNetworks();
        $page = PageHeadService::getCommonHead($ctx);
        $networks_selected = 0;

        foreach ($networks_list as &$net) {
            $net_set = $user->getPref('network_select_' . $net['id']);
            if (($net_set) || $net_set === false) {
                $net['selected'] = 1;
                $networks_selected++;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::page_index_post($ctx);
        }

        $items = $ctx->get('Items');

        $page['page'] = 'index';
        $page['head_name'] = $ncfg->get('web_title');
        $page['web_main']['scriptlink'][] = './scripts/index.js';

        /* Graph scripts
        * https://cdn.jsdelivr.net/npm/chart.js
        * https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js
        */

        $page['web_main']['scriptlink'][] = './scripts/chart.js';
        $page['web_main']['scriptlink'][] = './scripts/chartjs-adapter-date-fns.bundle.min.js';

        /* Include refresher/submitter script tpl */
        $page['web_main']['main_head_tpl'][] = 'refresher-js';
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
        $page['controls']['show_bookmarks_status'] = $show_bookmarks_status ? 1 : 0;
        $page['controls']['show_highlight_hosts_status'] = $show_highlight_hosts_status ? 1 : 0;
        $page['controls']['show_other_hosts_status'] = $show_other_hosts_status ? 1 : 0;
        $page['controls']['show_termlog_status'] = $show_termlog_status ? 1 : 0;

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
            $bookmarks = self::format_items($user, $items->getByType('bookmarks'));
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
        $page['networks_selected'] = $networks_selected;

        /* Webs Categories */
        $page['web_categories'] = $categories->getByType(2);

        return $page;
    }

    /**
     *
     * @param \User $user
     * @param array<string, mixed> $items_results
     * @return array<string, mixed>
     */
    private static function format_items(\User $user, array $items_results): array
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

    /**
     *
     * @param AppContext $ctx
     * @return bool
     */
    private static function page_index_post(AppContext $ctx): bool
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
}
