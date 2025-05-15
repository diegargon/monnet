<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<string> $lng
 * @var array<mixed> $tdata
 * @var User $user
 */
!defined('IN_WEB') ? exit : true;
?>

<div id="footer_hosts" class="footer_hosts">
    <div class="footer_host_container">
        <div id="hosts_footer_dropdown">
            <button id="hosts_footer_dropdown_btn" data-id="<?= $user->getId(); ?>">>
            </button>
        </div>
        <!-- Display / Totals -->
        <div id="host_totals" class="host_totals text_shadow_style1"></div>
        <div id="host_onoff" class="host_onoff text_shadow_style1"></div>
        <div id="last_refresher" class="last_refresher text_shadow_style1"></div>
        <div id="cli_last_run" class="cli_last_run text_shadow_style1"></div>
        <div id="discovery_last_run" class="discovery_last_run text_shadow_style1"></div>
        <div id="heartbeatLed" class="gateway-led" title="Gateway Status"></div>
        <!-- Total On/Off -->
    </div>
    <div
        id="footer_hosts_dropdown"
        style="<?= $user->getPref('footer_dropdown_status') ? 'display: inline-flex;' : 'color: blue'?>"
    >
        <div id="footer-dropdown-item-container" class="footer-dropdown-item-container"></div>
    </div>
</div>
