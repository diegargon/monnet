<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
?>
<form method="POST">
    <div class="options_container">
        <div class="services_ctrl">
            <input type="hidden" name="show_services" value="0" />
            <input class="check" type="checkbox" value="1" name="show_services" <?= $tdata['controls']['show_services_status'] ? ' checked ' : null ?> onchange="this.form.submit()" />
            <span class="opt_labels">Services</span>
        </div>
        <div class="bookmarks_ctrl">
            <input type="hidden" name="show_bookmarks" value="0" />
            <input class="check" type="checkbox" value="1" name="show_bookmarks" <?= $tdata['controls']['show_bookmarks_status'] ? ' checked ' : null; ?>  onchange="this.form.submit()" />
            <span class="opt_labels">Bookmarks</span>
        </div>
        <div class="hosts_ctrl">
            <input type="hidden" name="show_hosts" value="0" />
            <input class="check" type="checkbox" value="1" name="show_hosts" <?= $tdata['controls']['show_hosts_status'] ? ' checked ' : null ?> onchange="this.form.submit()" />
            <span class="opt_labels">Hosts</span>
        </div>
        <div class="system_ctrl">
            <input type="hidden" name="show_this_system" value="0" />
            <input class="check" type="checkbox" value="1" name="show_this_system" <?= $tdata['controls']['show_this_system_status'] ? ' checked ' : null ?>  onchange="this.form.submit()" />
            <span class="opt_labels">System</span>
        </div>
    </div>
    <!--
    <div class="profile_box">
        <select class="place_profile" name="profile_type" onchange="this.form.submit()">
        </select>
        <span class="opt_labels">Profile</span>
    </div>
    -->
</form>