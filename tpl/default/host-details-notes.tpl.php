<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
<div id="tab3" class="host-details-tab-content">
    <!-- <div class="textarea-bar"></div> -->
    <input type="number" id="host_note_id" style="display:none"
        readonly value="<?= $tdata['host_details']['notes_id'] ?>"/>
    <textarea
        id="textnotes"
        name="textnotes"
        rows="10"
        cols="100"><?= $tdata['host_details']['notes'] ?? '' ?></textarea>
</div>
