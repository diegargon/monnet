<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<int|string, mixed> $cfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="options-container">
    <!-- GENERAL -->
    <fieldset class="ctrl_fieldset">
        <legend class="ctrl_legend"><?= $lng['L_GENERAL'] ?></legend>

        <div class="user_ctrl">
            <a clasS="ctrl_link" href="/?page=logout">
                <img class="settigns_link"
                     src="./tpl/default/img/settings-items.png" title="<?= $lng['L_LOGOUT'] ?>">
            </a>
            <span class="opt_labels"><?= $tdata['username'] ?></span>
        </div>
        <div class="general_ctrl">
            <button id="toggleItemsSettings" class="button-ctrl" type="submit">
                <img class="settigns_link"
                     src="./tpl/default/img/settings-items.png" title="<?= $lng['L_EDIT'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_QUICK_CONFIG'] ?></span>
        </div>
        <div class="general_ctrl">
            <a class="ctrl_link" href="?page=settings">
                <img class="settigns_link"
                     src="./tpl/default/img/settings-items.png" title="<?= $lng['L_CONFIG'] ?>">
            </a>
            <span class="opt_labels"><?= $lng['L_CONFIG'] ?></span>
        </div>
    </fieldset>
    <!-- Add Items -->
    <fieldset class="ctrl_fieldset">
        <legend class="ctrl_legend"><?= $lng['L_ADD'] ?></legend>
        <div class="bookmarks_ctrl">
            <button id="submitBookmark"
                    onclick="submitCommand('mgmtBookmark',{id: 38, action: 'add'})"
                    class="button-ctrl" type="submit">
                <img class="add_link" src="./tpl/default/img/add.png" title="<?= $lng['L_ADD'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_BOOKMARKS'] ?></span>
        </div>
        <div class="addhost_ctrl">
            <button id="addHostBox" class="button-ctrl" type="submit" data-title="<?= $lng['L_REMOTE_HOST'] ?>">
                <img class="add_link" src="./tpl/default/img/add.png" title="<?= $lng['L_ADD'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_REMOTE_HOST'] ?></span>
        </div>
        <div class="general_ctrl">
            <button id="addNetwork" class="button-ctrl" type="submit">
                <img class="add_link"
                     src="./tpl/default/img/add.png" title="<?= $lng['L_NETWORK'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_NETWORKS'] ?></span>
        </div>
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
