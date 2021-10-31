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

<div class="options_container">
    <form method="POST">
        <div class="profile_box">
            <span class="opt_labels">Profile</span>
            <select class="place_profile" name="profile_type" onchange="this.form.submit()">

            </select><br/><br/>
        </div>
        <span class="opt_labels">Bookmarks</span>
        <input type="hidden" name="show_bookmarks" value="0" />
        <input class="check" type="checkbox" value="1" name="show_bookmarks" <?= $tdata['controls']['show_bookmarks_status'] ? ' checked ' : null; ?>  onchange="this.form.submit()" />

        <span class="opt_labels">System</span>
        <input type="hidden" name="show_this_system" value="0" />
        <input class="check" type="checkbox" value="1" name="show_this_system" <?= $tdata['controls']['show_this_system_status'] ? ' checked ' : null ?>  onchange="this.form.submit()" />
        <span class="opt_labels">Services</span>
        <input type="hidden" name="show_services" value="0" />
        <input class="check" type="checkbox" value="1" name="show_services" <?= $tdata['controls']['show_services_status'] ? ' checked ' : null ?> onchange="this.form.submit()" />

    </form>
</div>