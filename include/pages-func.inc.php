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
    $items = [];
    $theme = $user->getTheme();
    foreach ($items_results as $item) {
        $item_conf = json_decode($item['conf'], true);
        $item_img = '';
        if ($item_conf['image_type'] === 'favicon' && empty($item_conf['image_resource'])) {
            $item_img = $item_conf['url'] . '/favicon.ico';
            $item_img = cached_img($user, $item['id'], $item_img);
        } else if ($item_conf['image_type'] === 'favicon') {
            $favicon_path = $item_conf['image_resource'];
            $item_img = base_url($item_conf['url']) . '/' . $favicon_path;
            $item_img = cached_img($user, $item['id'], $item_img);
        } elseif ($item_conf['image_type'] === 'url') {
            $item_img = $item_conf['image_resource'];
            $item_img = cached_img($user, $item['id'], $item_img);
        } elseif ($item_conf['image_type'] === 'local_img') {
            $item_img = '/local_img/' . $item_conf['image_resource'];
        }

        $item['img'] = $item_img;
        $items[] = array_merge($item, $item_conf);
    }

    return $items;
}
