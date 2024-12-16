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
 * @var Config $ncfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */

!defined('IN_WEB') ? exit : true;
?>
<div class="settings-container" id="settings-container">
    <div class="settings-tabs-head-container">
        <button id="settings_tab_1" onclick="changeSettingsTab(1)" class="settings-tabs-head active"><?= $lng['L_GENERAL'];?></button>
        <?php if ($ncfg->get('ansible')) : ?>
        <button id="settings_tab_102" onclick="changeSettingsTab(102)" class="settings-tabs-head">Ansible</button>
        <?php endif; ?>
        <?php if ($ncfg->get('mail')) : ?>
        <button id="settings_tab_101" onclick="changeSettingsTab(101)" class="settings-tabs-head"><?= $lng['L_MAIL']?></button>
        <?php endif; ?>
        <!--
            <button id="settings_tab_3" onclick="changeSettingsTab(3)" class="settings-tabs-head">Apartado 3</button>
        -->
        <!--
            <button id="settings_tab_4" onclick="changeSettingsTab(4)" class="settings-tabs-head">Apartado 4</button>
        -->
        <div id="config_status_msg"></div>
    </div>
<?php
// Recorremos las configuraciones agrupadas por cada tab (solapa)
foreach ($tdata['groupedConfig'] as $tabId => $configs) {
    ?>
    <div id="settings_content_tab_<?= $tabId ?>" class="settings-tab-content <?= $tabId === 1 ? 'active' : '' ?>">
        <!-- <h3><?= $configs[0]['ctab_desc'] ?? null ?></h3>  -->
        <form id="config_<?= $tabId ?>">
        <?php foreach ($configs as $config) {
                $ctype = $config['ctype'];
                $ckey = $config['ckey'];
                $cvalue = $config['cvalue'];
                $cdesc = $config['cdesc'];
                $cdisplay = $config['cdisplay'];
            ?>
                <div class="config-field">
                    <label for="<?= $ckey ?>"><?= $cdisplay ?></label>
                    <?php
                    switch ($ctype) {
                        case 0: // string
                            ?>
                            <input type="text" id="<?= $ckey ?>" name="<?= $ckey ?>" value="<?= $cvalue ?>" />
                            <?php
                            break;
                        case 1: // int
                            ?>
                            <input type="number" id="<?= $ckey ?>" name="<?= $ckey ?>" value="<?= (int)$cvalue ?>" />
                            <?php
                            break;
                        case 2: // bool
                            ?>
                            <input type="hidden" name="<?= $ckey ?>" value="0">
                            <input
                                type="checkbox"
                                id="<?= $ckey ?>"
                                name="<?= $ckey ?>"
                                value="1"
                                <?= $cvalue ? 'checked' : '' ?>
                            />
                            <?php
                            break;
                        case 3: // float
                            ?>
                            <input
                                type="number"
                                id="<?= $ckey ?>"
                                name="<?= $ckey ?>"
                                value="<?= (float)$cvalue ?>" step="any"
                            />
                            <?php
                            break;
                        case 4: // date
                            ?>
                            <input type="date" id="<?= $ckey ?>" name="<?= $ckey ?>" value="<?= $cvalue ?>" />
                            <?php
                            break;
                        case 5: // url
                            ?>
                            <input type="url" id="<?= $ckey ?>" name="<?= $ckey ?>" value="<?= $cvalue ?>" />
                            <?php
                            break;
                        case 6: // dropdown
                            ?>
                            <select id="<?= $ckey ?>" name="<?= $ckey ?>">
                                <?php
                                foreach ($cvalue as $cvalue_key => $cvalue_value) {
                                    ?>
                                    <option
                                        value="<?= $cvalue_key ?>" <?= $cvalue_value === 1 ? 'selected' : '' ?>>
                                        <?= $cvalue_key ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <?php
                            break;
                        case 700: // password
                            ?>
                            <input type="password" id="<?= $ckey ?>" name="<?= $ckey ?>" value="<?= $cvalue ?>" />
                            <input
                                type="password"
                                id="<?= $ckey ?>_confirm" name="<?= $ckey ?>_confirm"
                                value="<?= $cvalue ?>"
                                placeholder="Confirmar contraseña"
                            />
                            <?php
                            break;
                        case 800: // email
                            ?>
                            <input type="email" id="<?= $ckey ?>" name="<?= $ckey ?>" value="<?= $cvalue ?>" />
                            <?php
                            break;
                        case 1000: // array
                            ?>
                            <textarea id="<?= $ckey ?>" name="<?= $ckey ?>"><?= json_encode($cvalue) ?></textarea>
                            <?php
                            break;
                        case 11: // array<array>
                            ?>
                            <textarea id="<?= $ckey ?>" name="<?= $ckey ?>"><?= json_encode($cvalue) ?></textarea>
                            <?php
                            break;
                        default:
                            echo "<!-- Tipo no soportado -->";
                            break;
                    }
                    ?>
                </div>
        <?php } ?>
            <button onclick="sendFormData(this)" class="button-submit" type="button"><?= $lng['L_SEND'];?></button>
        </form>
    </div>
    <?php
}
?>

</div>
