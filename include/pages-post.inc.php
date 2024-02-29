<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function page_index_post(AppCtx $ctx) {
    $page_data = [];
    $user = $ctx->getAppUser();

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
        post_bookmark($ctx, $page_data);
    }

    /* To REMOVE
      if (Filters::postInt('addNetworkForm')) {
      post_network($ctx, $page_data);
      }
     */

    return $page_data;
}

function post_bookmark(AppCtx $ctx, array &$page_data) {
    $lng = $ctx->getAppLang();
    $db = $ctx->getAppDb();

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
    } else if ($image_type == 'local_img') { //Only allow file.jpg
        $field_img = Filters::postCustomString('field_img', '.');
    } else { //favicon,
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
    if ($image_type == 'favicon' && empty($field_img) && !empty($_POST['field_img'])) {
        $page_data['error_msg'] = "{$lng['L_LINK']} {$lng['L_ERROR_INVALID']}";
    }
    if ($image_type == 'local_img' && empty($field_img)) {
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
    }
}

/* TO REMOVE
function post_network(AppCtx $ctx, array &$page_data) {
    $lng = $ctx->getAppLang();
    $db = $ctx->getAppDb();
    $network_name = Filters::postString('networkName');
    $network_ip = Filters::postIP('network');
    $network_cidr = Filters::postInt('networkCIDR');
    $network_vlan = Filters::postInt('networkVLAN');
    $network_scan = Filters::postInt('networkScan');

    if (empty($network_vlan)) {
        $network_vlan = 1;
    }
    if (empty($network_scan)) {
        $network_scan = 0;
    }
    //TODO check overlapping networks?

    if (empty($network_ip)) {
        $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_NETWORK']} {$lng['L_ERROR_EMPTY_INVALID']}";
    } else if (empty($network_name)) {
        $page_data['error_msg'] = "{$lng['L_FIELD']} {$lng['L_NAME']} {$lng['L_ERROR_EMPTY_INVALID']}";
    } else if (empty($network_cidr)) {
        $page_data['error_msg'] = "{$lng['L_FIELD']} .' CIDR '.  {$lng['L_ERROR_EMPTY_INVALID']}";
    }

    $network = $network_ip . '/' . $network_cidr;
    if (!Filters::varNetwork($network)) {
        $page_data['error_msg'] = "{$lng['L_NETWORK']} {$lng['L_ERROR_EMPTY_INVALID']}";
    }

    $page_data['networkName'] = $network_name;
    $page_data['network'] = $network_ip;
    $page_data['network_cidr'] = $network_cidr;
    $page_data['network_vlan'] = $network_vlan;
    $page_data['network_scan'] = $network_scan;

    if (empty($page_data['error_msg'])) {
        $set = ['name' => $network_name, 'network' => $network, 'vlan' => $network_vlan, 'scan' => $network_scan];
        $db->insert('networks', $set);
        $page_data['status_msg'] = 'OK';
    }
}
*/
