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
        <fieldset class="ctrl_fieldset">
            <legend class="ctrl_legend"><?= $lng['L_BOOKMARKS'] ?></legend>
            <div class="services_ctrl">
                <input type="hidden" name="show_applinks" value="0" />
                <input class="check" type="checkbox" value="1" name="show_applinks" <?= $tdata['controls']['show_applinks_status'] ? ' checked ' : null ?> onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_APPLINKS']; ?></span>
            </div>
            <div class="bookmarks_ctrl">
                <input type="hidden" name="show_bookmarks" value="0" />
                <input class="check" type="checkbox" value="1" name="show_bookmarks" <?= $tdata['controls']['show_bookmarks_status'] ? ' checked ' : null; ?>  onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_BOOKMARKS'] ?></span>
            </div>
        </fieldset>
        <fieldset class="ctrl_fieldset">
            <legend class="ctrl_legend"><?= $lng['L_HOSTS'] ?></legend>
            <div class="hosts_highlight_ctrl">
                <input type="hidden" name="show_highlight_hosts" value="0" />
                <input class="check" type="checkbox" value="1" name="show_highlight_hosts" <?= $tdata['controls']['show_highlight_hosts_status'] ? ' checked ' : null ?> onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_HIGHLIGHT_HOSTS']; ?></span>
            </div>
            <div class="hosts_ctrl">
                <input type="hidden" name="show_rest_hosts" value="0" />
                <input class="check" type="checkbox" value="1" name="show_rest_hosts" <?= $tdata['controls']['show_other_hosts_status'] ? ' checked ' : null ?> onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_OTHERS'] ?></span>
            </div>
        </fieldset>
        <!--
                <div class="system_ctrl">
                    <input type="hidden" name="show_this_system" value="0" />
                    <input class="check" type="checkbox" value="1" name="show_this_system" <?= $tdata['controls']['show_this_system_status'] ? ' checked ' : null ?>  onchange="this.form.submit()" />
                    <span class="opt_labels">This</span>
                </div>
        -->
    </div>
    <!--
    <div class="profile_box">
        <select class="place_profile" name="profile_type" onchange="this.form.submit()">
        </select>
        <span class="opt_labels">Profile</span>
    </div>
    -->
</form>