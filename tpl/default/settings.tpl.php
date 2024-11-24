<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="settings-container" id="settings-container">
    <div class="settings-tabs-head-container">
        <button id="settings_tab_1" onclick="changeSettingsTab(1)" class="settings-tabs-head">Apartado 1</button>
        <button id="settings_tab_2" onclick="changeSettingsTab(2)" class="settings-tabs-head">Apartado 2</button>
        <button id="settings_tab_3" onclick="changeSettingsTab(3)" class="settings-tabs-head">Apartado 3</button>
        <button id="settings_tab_4" onclick="changeSettingsTab(4)" class="settings-tabs-head">Apartado 4</button>
    </div>
    <div id="settings_content_tab_1" class="settings-tab-content">
        <h3>Apartado 1</h3>
        <p>Contenido del apartado 1.</p>
    </div>
    <div id="settings_content_tab_2" onclick="changeSettingsTab(2)" class="settings-tab-content">
        <h3>Apartado 2</h3>
        <p>Contenido del apartado 2.</p>
    </div>
    <div id="settings_content_tab_3" onclick="changeSettingsTab(3)" class="settings-tab-content">
        <h3>Apartado 3</h3>
        <p>Contenido del apartado 3.</p>
    </div>
    <div id="settings_content_tab_4" onclick="changeSettingsTab(4)" class="settings-tab-content">
        <h3>Apartado 4</h3>
        <p>Contenido del apartado 4.</p>
    </div>
</div>

