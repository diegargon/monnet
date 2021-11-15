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
            <img class="hosts-item" src="<?= $tdata['host_details']['online_image'] ?>" alt="<?= $tdata['host_details']['alt_online'] ?>"/>
            <?php if (!empty($tdata['host_details']['system_image'])) { ?>
                <img class="fab" src="<?= $tdata['host_details']['system_image'] ?>" alt="<?= $tdata['host_details']['system_name'] ?>"/>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['img_ico'])) { ?>
                <img class="fab" src="<?= $tdata['host_details']['img_ico'] ?>" alt=""/>
            <?php } ?>
            <div class="host-item"><?= $tdata['host_details']['title'] ?> </div>
            <?php if (!empty($tdata['host_details']['hostname'])) { ?>
                <div class="host-item"><?= $tdata['host_details']['hostname'] ?> </div>
            <?php } ?>
            <div class="host-item"><?= $tdata['host_details']['ip'] ?></div>
            <?php if (!empty($tdata['host_details']['mac'])) { ?>
                <div class="host-item"><?= $tdata['host_details']['mac'] ?> </div>
            <?php } ?>
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
        <div class="host-details-close">
            <form class="host-details-form-close" method="POST">
                <input type="submit" name="close_host_details" value="Cerrar"/>
            </form>
        </div>
        <div id="progress_bars">
            <label for="load_avg">Load:</label>
            <progress id="load_avg" value="50" max="100">50%</progress>
            <label for="mem">Mem:</label>
            <progress id="mem" value="90" max="100">90%</progress>
            <label for="sda">sda:</label>
            <progress id="sda" value="30" max="100">30%</progress>
            <label for="sdb">sdb:</label>
            <progress id="sdb" value="30" max="100">30%</progress>
            <div class="" >
                <label class="created_label">Unido:</label>
                <span class="created"><?= $tdata['host_details']['formated_creation_date'] ?></span>
            </div>
            <?php if (!empty($tdata['host_details']['latency_ms'])) { ?>
                <div class="" >
                    <label class="latency">Latencia:</label>
                    <span class="latency"><?= $tdata['host_details']['latency_ms'] ?></span>
                </div>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['formated_last_seen'])) { ?>
                <div class="" >
                    <?php if (empty($tdata['host_details']['online'])) { ?>
                        <label class="last_seen_label">Ultima vez visto:</label>
                    <?php } else { ?>
                        <label class="connected_label">Ultima comprobacion:</label>
                    <?php } ?>
                    <span class="connected_date"><?= $tdata['host_details']['formated_last_seen'] ?> </span>
                </div>
            <?php } ?>
        </div>
        <div class="charts">
            <label class="none_opt">None</label>
            <input type="radio" checked name="graph_choice" value="none_graph">
            <label class="network_opt">Network</label>
            <input type="radio" name="graph_choice" value="network_graph">
            <label class="ping_opt">Ping</label>
            <input type="radio" name="graph_choice" value="ping_graph">
            <label class="logs_opt">Logs</label>
            <input type="radio" name="graph_choice" value="show_logs">
        </div>
        <!-- DEPLOYS -->
        <?php
        if (!empty($tdata['host_details']['deploys']) && valid_array($tdata['host_details']['deploys'])) {
            ?>
            <div class="deploy_container">
                <form id="deploy_form" method="POST">
                    <select class="select_deploy" name="deploy_option">
                        <option value="0"></option>
                        <?php
                        foreach ($tdata['host_details']['deploys'] as $k_deploy => $deploy) {
                            ?>
                            <option value="<?= $k_deploy ?>"><?= $deploy['name'] ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <input class="deploy_btn" type="submit" name="deploy" value="Deploy">
                </form>
            </div>
            <?php
        }
        ?>
        <!--
              <div class="ping_chart">
            <img alt="" src="tpl/default/img/graph.png"/>
        </div>
        -->
    </div> <!-- host-details-container -->
</div> <!-- host-details -->