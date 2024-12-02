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
<script>
    function sendFormData(button) {
        event.preventDefault();
        // Encontrar el formulario padre del botón que disparó el evento
        let form = $(button).closest('form');

        // Obtener los datos del formulario como un array de objetos
        let formData = form.serializeArray();

        let commandValues = {};
        formData.forEach(function (item) {
            commandValues[item.name] = item.value;
        });
        commandValues.id = 0;

        var requestData = {
            command: 'submitform',
            command_values: commandValues
        };
        $.post('submitter.php', requestData, function (response) {
            console.log('Respuesta del servidor:', response);
        });
    }
</script>

<div class="settings-container" id="settings-container">

    <div class="settings-tabs-head-container">
        <button id="settings_tab_1" onclick="changeSettingsTab(1)" class="settings-tabs-head">General</button>
        <button id="settings_tab_2" onclick="changeSettingsTab(2)" class="settings-tabs-head">Apartado 2</button>
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
    <div id="settings_content_tab_<?= $tabId ?>" class="settings-tab-content">
        <!-- <h3><?= $configs[0]['ctab_desc'] ?? null ?></h3>   descripción de la tab -->
        <form id="config_<?= $tabId ?>">
            <?php foreach ($configs as $config) {
                $ctype = $config['ctype'];
                $ckey = $config['ckey'];
                $cvalue = $config['cvalue'];
                $cdesc = $config['cdesc']; // Descripción del campo
            ?>
                <div class="config-field">
                    <label for="<?= $ckey ?>"><?= $cdesc ?></label>
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
                            <input
                                type="checkbox"
                                id="<?= $ckey ?>"
                                name="<?= $ckey ?>"
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
                                // Supongo que $config['options'] contiene las opciones para el dropdown
                                foreach ($config['options'] as $option) {
                                    ?>
                                    <option
                                        value="<?= $option ?>" <?= $option == $cvalue ? 'selected' : '' ?>>
                                        <?= $option ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <?php
                            break;
                        case 7: // password
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
                        case 8: // email
                            ?>
                            <input type="email" id="<?= $ckey ?>" name="<?= $ckey ?>" value="<?= $cvalue ?>" />
                            <?php
                            break;
                        case 10: // array
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
            <button onclick="sendFormData(this)" class="button-submit" type="button">Submit</button>
        </form>
    </div>
    <?php
}
?>

</div>

