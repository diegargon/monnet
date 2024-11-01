<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var array<string> $cfg
 */
/**
 * @var array<string> $lng
 */
/**
 * @var array<string> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="options_container">

    <!-- GENERAL -->
    <fieldset class="ctrl_fieldset">
        <legend class="ctrl_legend"><?= $lng['L_GENERAL'] ?></legend>

        <div class="general_ctrl">
            <button id="addNetwork" class="button-ctrl" type="submit">
                <img class="add_link"
                     src="./tpl/default/img/add.png" title="<?= $lng['L_ADD'] . ' ' . $lng['L_NETWORK'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_ADD'] . ' ' . $lng['L_NETWORK'] ?></span>
        </div>
        <div class="general_ctrl">
            <button id="toggleItemsSettings" class="button-ctrl" type="submit">
                <img class="settigns_link"
                     src="./tpl/default/img/settings-items.png" title="<?= $lng['L_SETTINGS'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_QUICK_SETTINGS'] ?></span>
        </div>
    </fieldset>

    <!-- HOSTS -->
    <fieldset class="ctrl_fieldset">
        <legend class="ctrl_legend"><?= $lng['L_HOSTS'] ?></legend>

        <div class="addhost_ctrl">
            <button id="addHostBox" class="button-ctrl" type="submit" data-title="<?= $lng['L_ADD_REMOTE_HOST'] ?>">
                <img class="add_link" src="./tpl/default/img/add.png" title="<?= $lng['L_ADD'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_ADD'] . ' R' . $lng['L_HOST'] ?></span>
        </div>
        <form method="POST" name="host_form">
            <div class="hosts_highlight_ctrl">
                <input type="hidden" name="show_highlight_hosts" value="0" />
                <input class="check" type="checkbox" value="1"  name="show_highlight_hosts"
                <?= $tdata['controls']['show_highlight_hosts_status'] ? ' checked ' : null ?>
                       onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_HIGHLIGHT_HOSTS']; ?></span>
            </div>
            <div class="hosts_ctrl">
                <input type="hidden" name="show_rest_hosts" value="0" />
                <input class="check" type="checkbox" value="1" name="show_rest_hosts"
                <?= $tdata['controls']['show_other_hosts_status'] ? ' checked ' : null ?>
                       onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_OTHERS'] ?></span>
            </div>
        </form>
    </fieldset>

    <!-- BOOKMARKS -->
    <fieldset class="ctrl_fieldset">
        <legend class="ctrl_legend"><?= $lng['L_BOOKMARKS'] ?></legend>
        <div class="bookmarks_ctrl">
            <button id="addBookmark" class="button-ctrl" type="submit">
                <img class="add_link" src="./tpl/default/img/add.png" title="<?= $lng['L_ADD'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_ADD'] ?></span>
        </div>
        <form method="POST" name="links_form">
            <div class="bookmarks_ctrl">
                <input type="hidden" name="show_bookmarks" value="0" />
                <input class="check" type="checkbox" value="1" name="show_bookmarks"
                <?= $tdata['controls']['show_bookmarks_status'] ? ' checked ' : null; ?>
                       onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_BOOKMARKS'] ?></span>
            </div>
        </form>
    </fieldset>
    <!--
            <div class="system_ctrl">
                <input type="hidden" name="show_this_system" value="0" />
                <input class="check" type="checkbox" value="1" name="show_this_system"
                         $tdata['controls']['show_this_system_status'] ? ' checked ' : null
                onchange="this.form.submit()" />
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
