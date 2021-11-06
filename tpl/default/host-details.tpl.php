<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<div id="host-details" class="host-details">
    <div id="host-details-container" class="host-details-container">

        <div class="host-details-main">
            <img class="hosts-online" src="<?= $tdata['host_details']['online_image'] ?>" alt="<?= $tdata['host_details']['alt_online'] ?>"/>
            <?php if (!empty($tdata['host_details']['os_image'])) { ?>
                <img class="fab" src="<?= $tdata['host_details']['os_image'] ?>" alt="<?= $tdata['host_details']['os_name'] ?>"/>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['img_ico'])) { ?>
                <img class="fab" src="<?= $tdata['host_details']['img_ico'] ?>" alt=""/>
            <?php } ?>
            <div class="host-title"><?= $tdata['host_details']['title'] ?> </div>
            <?php if (!empty($tdata['host_details']['hostname'])) { ?>
                <div class="host-hostname"><?= $tdata['host_details']['hostname'] ?> </div>
            <?php } ?>
            <div class="host-ip"><?= $tdata['host_details']['ip'] ?></div>
        </div> <!-- host-details-main -->

        <?php
        if (!empty($tdata['host_details']['host_ports'])) {
            ?>
            <div class="host_port_container">
                <?php foreach ($tdata['host_details']['host_ports'] as $port) { ?>
                    <div class="port_container">
                        <?php if ($port['online']) { ?>
                            <img class="port-online" src="tpl/<?= $tdata['theme'] ?>/img/green.png" alt=""/>
                        <?php } else { ?>
                            <img class="port-offline" src="tpl/<?= $tdata['theme'] ?>/img/red.png" alt=""/>
                        <?php } ?>
                        <div class="host_port_name"><?= $port['title'] ?></div>
                        <div class="host_port">(<?= $port['port'] ?>)</div>
                    </div> <!-- port container -->
                <?php } ?>
            </div> <!-- host port container -->
        <?php } ?>

        <div class="host-controls">
            <?php if (!empty($tdata['host_details']['wol']) && empty($tdata['host_details']['online'])) { ?>
                <input onClick="refresh('power_on', <?= $tdata['host_details']['id'] ?>)" type="image" class="power-off" src="tpl/<?= $tdata['theme'] ?>/img/power-off.png" alt="poff" title="turn on"/>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['access_method']) && !empty($tdata['host_details']['online'])) { ?>
                <input onClick="refresh('power_off', <?= $tdata['host_details']['id'] ?>)" type="image" class="power-on" src="tpl/<?= $tdata['theme'] ?>/img/power-on.png" alt="pon" title="turn off"/>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['access_method'])) { ?>
                <input onClick="refresh('reboot', <?= $tdata['host_details']['id'] ?>)" type="image" class="reboot" src="tpl/<?= $tdata['theme'] ?>/img/reboot.png" alt="pon" title="reboot"/>
            <?php } ?>
        </div> <!--host-controls -->
    </div> <!-- host-details-container -->
</div> <!-- host-details -->