<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
<div id="tab10" class="host-details-tab-content">
    <div id="graphs_container" class="graphs_container">
        <?= !empty($tdata['host_details']['ping_graph']) ? $tdata['host_details']['ping_graph'] : null ?>
    </div>
</div>
