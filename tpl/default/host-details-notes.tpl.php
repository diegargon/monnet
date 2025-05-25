<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/*
$tdata['bitacora'] = [
    ['date' => '2024-06-01 10:00', 'msg' => 'Arranque del sistema realizado correctamente.', 'username' => 'monnet'],
    ['date' => '2024-06-01 12:15', 'msg' => 'Actualización de software aplicada.', 'username' => 'monnet'],
    ['date' => '2024-06-01 15:30', 'msg' => 'Backup completado sin errores.', 'username' => 'monnet'],
    ['date' => '2024-06-01 16:10', 'msg' => 'Servicio web reiniciado.', 'username' => 'monnet'],
    ['date' => '2024-06-01 17:00', 'msg' => 'Se agregó nueva regla de firewall.', 'username' => 'monnet'],
    ['date' => '2024-06-01 19:05', 'msg' => 'Usuario soporte realizó cambios en configuración.', 'username' => 'monnet'],
    ['date' => '2024-06-01 20:00', 'msg' => 'Bitácora de prueba: entrada automática.', 'username' => 'monnet'],
];
*/
?>
<div id="tab3" class="host-details-tab-content">
    <div class="bitacora-container">
        <div class="status_msg"></div>
        <input
            type="text"
            id="bitacora_entry"
            name="bitacora_entry"
            placeholder="Nueva entrada de bitácora"
            style="width:70%;"
            maxlength="255"
        />
        <button type="button" id="btn_bitacora_entry"><?= $lng['L_ADD']?></button>
    </div>
    <!-- Tabla de entradas de bitácora -->
    <?php if (!empty($tdata['bitacora']) && is_array($tdata['bitacora'])): ?>
    <div class="bitacora-table-container" style="margin-top:10px; height:20vh; overflow-y:auto;">
        <table>
            <thead>
                <tr>
                    <th style="width:1%;">Fecha</th>
                    <th style="width:1%;">Usuario</th>
                    <th style="width:auto;">Mensaje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tdata['bitacora'] as $entry): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= $entry['date'] ?></td>
                        <td style="white-space:nowrap;"><?= $entry['username'] ?></td>
                        <td><?= $entry['msg'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="notes-container">
        <span><?= $lng['L_NOTES'] ?></span>
        <input type="number" id="host_note_id" style="display:none"
            readonly value="<?= $tdata['host_details']['notes_id'] ?>"/>
        <textarea
            id="textnotes"
            name="textnotes"
            rows="10"
            cols="50"><?= $tdata['host_details']['notes'] ?? '' ?></textarea>
    </div>
</div>
