<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
?>
<div id="add-network-container" style="<?= isset($tdata['show_add_network']) ? 'display:block;' : null ?>">
    <div class="front-container-bar">
        <button id="close_addnetwork" class="button-ctrl" type="submit"><img class="closelink" src="./tpl/<?= $cfg['theme'] ?>/img/close.png" title="<?= $lng['L_CLOSE'] ?>"></button>
        <div class="front-container-bar-title"><?= $lng['L_ADD'] . ' ' . $lng['L_NETWORK'] ?></div>
    </div>
    <div class="form_container">
        <div id="status_msg"><?= isset($tdata['status_msg']) ? $tdata['status_msg'] : null ?></div>
        <div id="error_msg"><?= isset($tdata['error_msg']) ? $tdata['error_msg'] : null ?></div>
        <form id="addNetworkForm" method="POST">
            <input type="hidden"  name="addNetworkForm" value="1" readonly/>
            <label for="networkName"><?= $lng['L_NAME'] ?>:</label>
            <input type="text" id="networkName" name="networkName"  size="12" maxlength="12" required value="<?= !empty($tdata['networkName']) ? $tdata['networkName'] : null ?>">

            <label for="network_cidr"><?= $lng['L_NETWORK'] ?>(CIDR)</label>
            <input type="text" id="network_cidr" name="network_cidr" size="32" maxlength="450" required value="<?= !empty($tdata['network_cidr']) ? $tdata['network_cidr'] : null ?>">

            <label for="network_scan"><?= $lng['L_SCAN'] ?></label>
            <input type="checkbox" name="network_scan" checked/>
            <label for="network_vlan"><?= $lng['L_VLAN'] ?></label>
            <input type="text" id="network_vlan" name="network_vlan" size="5" maxlength="5" required value="<?= !empty($tdata['network_vlan']) ? $tdata['network_vlan'] : 1 ?>">

            <!-- Botón para enviar el formulario -->
            <input type="submit" value="<?= $lng['L_ADD'] ?>"/>
        </form>
    </div>
</div>

