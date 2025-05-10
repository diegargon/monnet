<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var Config $ncfg
 * @var array<string> $lng Language data
 * @var array<mixed> $tdata Template Data
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="options-container">
    <!-- GENERAL -->
    <fieldset class="ctrl_fieldset">
        <legend class="ctrl_legend"><?= $lng['L_GENERAL'] ?></legend>

        <div class="user_ctrl">
            <a class="ctrl_link" href="/?page=user">
                <img class="settings_link"
                     src="./tpl/default/img/settings-items.png" title="<?= $lng['L_USER'] ?>">
            </a>
            <span class="opt_labels"><?= ucfirst($tdata['username']) ?></span>
        </div>
        <div class="general_ctrl">
            <button id="toggleItemsSettings" class="button-ctrl" type="submit">
                <img class="settings_link"
                     src="./tpl/default/img/settings-items.png" title="<?= $lng['L_EDIT'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_QUICK_CONFIG'] ?></span>
        </div>
        <div class="general_ctrl">
            <a class="ctrl_link" href="?page=settings">
                <img class="settings_link"
                     src="./tpl/default/img/settings-items.png" title="<?= $lng['L_CONFIG'] ?>">
            </a>
            <span class="opt_labels"><?= $lng['L_CONFIG'] ?></span>
        </div>
        <div class="general_ctrl">
            <button id="addNetwork"
                    onclick="submitCommand('mgmtNetworks',{id: 0, action: 'mgmt'})"
                    class="button-ctrl" type="submit">
                <img class="add_link"
                     src="./tpl/default/img/settings-items.png" title="<?= $lng['L_NETWORK'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_NETWORKS'] ?></span>
        </div>
    </fieldset>
    <!-- Add Items -->
    <fieldset class="ctrl_fieldset">
        <legend class="ctrl_legend"><?= $lng['L_ADD'] ?></legend>
        <div class="bookmarks_ctrl">
            <button id="submitBookmark"
                    onclick="submitCommand('mgmtBookmark',{id: 0, action: 'add'})"
                    class="button-ctrl" type="submit">
                <img class="add_link" src="./tpl/default/img/add.png" title="<?= $lng['L_ADD'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_BOOKMARKS'] ?></span>
        </div>
        <div class="addhost_ctrl">
            <button id="addHostBox" class="button-ctrl" type="submit" data-title="<?= $lng['L_ADD_HOST'] ?>">
                <img class="add_link" src="./tpl/default/img/add.png" title="<?= $lng['L_ADD'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_ADD_HOST'] ?></span>
        </div>
        <div class="general_ctrl">
            <button id="requestPool"
                    onclick="submitCommand('requestPool',{id: 0, action: 'mgmt'})"
                    class="button-ctrl" type="submit">
                <img class="add_link"
                     src="./tpl/default/img/add.png" title="<?= $lng['L_NETWORK'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_IP_POOL'] ?></span>
        </div>
    </fieldset>
    <fieldset class="ctrl_fieldset">
        <legend class="ctrl_legend"><?= $lng['L_INFO'] ?></legend>
        <div class="general_ctrl">
            <button id="alarms"
                    onclick="submitCommand('showAlarms',{id: 0, action: 'show'})"
                    class="button-ctrl" type="submit">
                <img class="settings_link"
                     src="./tpl/default/img/info.png" title="<?= $lng['L_ALARMS'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_ALARMS'] ?></span>
        </div>
        <div class="general_ctrl">
            <button id="events"
                    onclick="submitCommand('showEvents',{id: 0, action: 'show'})"
                    class="button-ctrl" type="submit">
                <img class="settings_link"
                     src="./tpl/default/img/info.png" title="<?= $lng['L_ALARMS'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_EVENTS'] ?></span>
        </div>
    </fieldset>

    <fieldset class="ctrl_fieldset">
        <legend class="ctrl_legend">Gateway</legend>
        <div class="general_ctrl">
            <button id="restart-gateway"
                    onclick="submitCommand('',{id: 0, action: 'restart-daemon'})"
                    class="button-ctrl" type="submit">
                <img class="settings_link"
                     src="./tpl/default/img/refresh.png" title="<?= $lng['L_RESTART_G'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_REBOOT'] ?></span>
        </div>
        <div class="general_ctrl">
            <button id="refresh-pbmeta"
                    onclick="submitCommand('',{id: 0, action: 'refresh-pbmeta'})"
                    class="button-ctrl" type="submit">
                <img class="settings_link"
                     src="./tpl/default/img/refresh.png" title="<?= $lng['L_REFRESH_PBMETA'] ?>">
            </button>
            <span class="opt_labels"><?= $lng['L_REFRESH_PBMETA'] ?></span>
        </div>
    </fieldset>
</div>
