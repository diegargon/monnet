<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
/**
 * Deal with form post
 *
 * @param AppContext $ctx
 * @return bool
 */
function page_index_post(AppContext $ctx): bool
{
    $user = $ctx->get('User');

    $profile_type = Filters::postString('profile_type');
    $show_bookmarks = Filters::postInt('show_bookmarks');
    $show_highlight_hosts = Filters::postInt('show_highlight_hosts');
    $show_other_hosts = Filters::postInt('show_rest_hosts');
    $show_termlog = Filters::postInt('show_termlog');
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
