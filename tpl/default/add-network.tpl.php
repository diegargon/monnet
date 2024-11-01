<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var array<string> $cfg
 */
/**
 * @var array<string> $lng
 */
/**
 * @var array<string> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>
<div id="add-network-container" class="draggable" style="<?= isset($tdata['show_add_network']) ? 'display:block;' : null ?>">
    <div class="front-container-bar dragbar">
        <button id="close_addnetwork" class="button-ctrl" type="submit">
            <img class="close_link" src="./tpl/<?= $cfg['theme'] ?>/img/close.png" title="<?= $lng['L_CLOSE'] ?>">
        </button>
        <div class="front-container-bar-title"><?= $lng['L_ADD'] . ' ' . $lng['L_NETWORK'] ?></div>
    </div>
    <div class="form_container">
        <div id="network_status_msg"><?= isset($tdata['status_msg']) ? $tdata['status_msg'] : null ?></div>
        <div id="network_error_msg"><?= isset($tdata['error_msg']) ? $tdata['error_msg'] : null ?></div>
        <table>
            <tr>
                <td><label for="networkName"><?= $lng['L_NAME'] ?>:</label></td>
                <td>
                    <input type="text" id="networkName" name="networkName"  size="32"
                           maxlength="255" required
                           value="<?= !empty($tdata['networkName']) ? $tdata['networkName'] : null ?>">
                </td>
            </tr>
            <tr>
                <td><label for="network"><?= $lng['L_NETWORK'] ?></label></td>
                <td>
                    <input type="text" id="network" name="network" size="13"
                           maxlength="13" required value="<?= !empty($tdata['network']) ? $tdata['network'] : null ?>">
                </td>
            </tr>
            <tr>
                <td><label for="network_cidr">CIDR</label></td>
                <td><input type="text" id="network_cidr" name="networkCIDR" size="2" maxlength="2"
                           required value="<?= !empty($tdata['network_cidr']) ? $tdata['network_cidr'] : null ?>">
                </td>
            </tr>
            <tr>
                <td><label for="network_scan"><?= $lng['L_SCAN'] ?></label></td>
                <td>
                    <input type="hidden" name="networkScan" value="0" />
                    <input type="checkbox" name="networkScan" value="1" checked/>
                </td>
            </tr>
            <tr>
                <td><label for="network_vlan"><?= $lng['L_VLAN'] ?></label></td>
                <td><input type="text" id="network_vlan" name="networkVLAN" size="5" maxlength="5"
                           required value="<?= !empty($tdata['network_vlan']) ? $tdata['network_vlan'] : 1 ?>">
                </td>
            </tr>
        </table>
        <button id="submitNetwork" type="submit"/><?= $lng['L_ADD'] ?></button>
    </div>
</div>

