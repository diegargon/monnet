<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
<div id="tab3" class="host-details-tab-content">
    <div class="bitacora-container">
        <div class="status_msg"></div>
        <input
            type="text"
            id="bitacora_entry"
            name="bitacora_entry"
            placeholder="<?= $lng['L_BITACORA_ENTRY'] ?>"
            style="width:70%;"
            maxlength="255"
        />
        <button type="button" id="btn_bitacora_entry"><?= $lng['L_BITACORA_ADD'] ?></button>
    </div>
    <div class="bitacora-table-container" style="margin-top:10px; max-height:20vh; overflow-y:auto;">
        <table>
            <thead>
                <tr>
                    <th style="width:1%;"><?= $lng['L_DATE'] ?></th>
                    <th style="width:1%;"><?= $lng['L_USER'] ?></th>
                    <th style="width:auto;"><?= $lng['L_MESSAGE'] ?? 'Mensaje' ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td>-</td><td>System</td><td colspan="3"><?= $lng['L_BITACORA_NO_ENTRIES'] ?></td></tr>
            </tbody>
        </table>
    </div>
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
