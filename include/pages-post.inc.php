<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function page_index_post(Database $db, User $user, Categories $categories, array $lng) {
    $page_data = [];

    $profile_type = Filters::postString('profile_type');
    $show_bookmarks = Filters::postInt('show_bookmarks');
    $show_this_system = Filters::postInt('show_this_system');
    $show_highlight_hosts = Filters::postInt('show_highlight_hosts');
    $show_other_hosts = Filters::postInt('show_rest_hosts');
    //add Item


    if ($profile_type !== false) {
        $user->setPref('profile_type', $profile_type);
    }
    if ($show_bookmarks !== false) {
        $user->setPref('show_bookmarks_status', $show_bookmarks);
    }
    if ($show_this_system !== false) {
        $user->setPref('show_this_system_status', $show_this_system);
    }
    if ($show_highlight_hosts !== false) {
        $user->setPref('show_highlight_hosts_status', $show_highlight_hosts);
    }
    if ($show_other_hosts !== false) {
        $user->setPref('show_other_hosts_status', $show_other_hosts);
    }

    if (Filters::postInt('addBookmarkForm')) {
        post_bookmark($db, $lng, $page_data);
    }

    if (Filters::postInt('addNetworkForm')) {
        post_network($db, $page_data);
    }

    return $page_data;
}

function post_bookmark(Database $db, array $lng, array &$page_data) {
    $bookmarkName = Filters::postString('bookmarkName');
    $cat_id = Filters::postInt('cat_id');
    $urlip = Filters::postUrl('urlip');

    if (!$urlip) {
        $urlip = Filters::postIP('urlip');
    }

    //TODO check valid image name.
    $image_type = Filters::postString('image_type');
    if ($image_type == 'image_resource') { //Remote Image
        $field_img = Filters::postImgUrl('field_img');
    } else { //favicon, local image
        $field_img = Filters::postPathFile('field_img');
    }

    $weight = Filters::postInt('weight');

    $page_data['show_add_bookmark'] = 1;

    if (empty($bookmarkName)) {
        $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_NAME']} {$lng['L_ERROR_EMPTY_INVALID']}";
    } else if (empty($urlip)) {
        $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_URLIP']} {$lng['L_ERROR_EMPTY_INVALID']}";
    } else if (empty($cat_id)) {
        $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_TYPE']} {$lng['L_ERROR_EMPTY_INVALID']}";
    } else if (empty('weight')) {
        $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_WEIGHT']} {$lng['L_ERROR_EMPTY_INVALID']}";
    } else if (empty($image_type)) {
        $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_IMAGE_TYPE']} {$lng['L_ERROR_EMPTY_INVALID']}";
    }

    if ($image_type != 'favicon' && empty($field_img)) {
        $page_data['error_msg'] = "{$lng['L_LINK']} {$lng['L_ERROR_EMPTY_INVALID']}";
    }

    $page_data['bookmarkName'] = $bookmarkName;
    $page_data['cat_id'] = $cat_id;
    $page_data['urlip'] = $urlip;
    $page_data['image_type'] = $image_type;
    $page_data['field_img'] = $field_img;
    $page_data['weight'] = $weight;
    if (empty($page_data['error_msg'])) {
        $conf = ['url' => $urlip, 'image_type' => $image_type, 'image_resource' => $field_img];
        $set = ['cat_id' => $cat_id, 'type' => 'bookmarks', 'title' => $bookmarkName, 'conf' => json_encode($conf), 'weight' => $weight];
        $db->insert('items', $set);
        $page_data['status_msg'] = 'OK';
    } else {
        $page_data['bookmarkName'] = $bookmarkName;
        $page_data['cat_id'] = $cat_id;
        $page_data['urlip'] = $urlip;
        $page_data['image_type'] = $image_type;
        $page_data['field_img'] = $field_img;
        $page_data['weight'] = $weight;
    }
}

function post_network(Database $db, array &$page_data) {

}
