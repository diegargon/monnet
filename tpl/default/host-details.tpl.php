<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
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
        </div> <!-- host-details-main -->
        <div class="host-details-main">
            <div class="host-item"><?= $tdata['host_details']['ip'] ?></div>
            <?php if (!empty($tdata['host_details']['mac'])) { ?>
                <div class="host-item"><?= $tdata['host_details']['mac'] ?> </div>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['mac_vendor']) && $tdata['host_details']['mac_vendor'] != '-') { ?>
                <div class="host-item"><?= $tdata['host_details']['mac_vendor'] ?> </div>
            <?php } ?>
        </div>
        <?php
        if (!empty($tdata['host_details']['ports'])) {
            ?>
            <div class="host_port_container">
                <?php foreach ($tdata['host_details']['ports'] as $port) { ?>
                    <div class="port_container">
                        <?php if ($port['online']) { ?>
                            <img class="port-online" src="tpl/<?= $tdata['theme'] ?>/img/green.png" alt=""/>
                        <?php } else { ?>
                            <img class="port-offline" src="tpl/<?= $tdata['theme'] ?>/img/red.png" alt=""/>
                        <?php } ?>
                        <div class="host_port_name"><?= $port['title'] ?></div>
                        <div class="host_port">(<?= $port['n'] ?>)</div>
                    </div> <!-- port container -->
                <?php } ?>
            </div> <!-- host port container -->
        <?php } ?>

        <div class="host-controls">
            <?php if (!empty($tdata['host_details']['wol']) && empty($tdata['host_details']['online'])) { ?>
                <input onClick="refresh('power_on', <?= $tdata['host_details']['id'] ?>)" type="image" class="action-icon power-off" src="tpl/<?= $cfg['theme'] ?>/img/power-off.png" alt="poff" title="turn on"/>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['access_method']) && !empty($tdata['host_details']['online'])) { ?>
                <input onClick="refresh('power_off', <?= $tdata['host_details']['id'] ?>)" type="image" class="action-icon power-on" src="tpl/<?= $cfg['theme'] ?>/img/power-on.png" alt="pon" title="turn off"/>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['access_method'])) { ?>
                <input onClick="refresh('reboot', <?= $tdata['host_details']['id'] ?>)" type="image" class="action-icon reboot" src="tpl/<?= $cfg['theme'] ?>/img/reboot.png" alt="reboot" title="reboot"/>
            <?php } ?>
            <input onClick="refresh('remove_host', <?= $tdata['host_details']['id'] ?>)" type="image"  class="action-icon remove" src="tpl/<?= $cfg['theme'] ?>/img/remove.png" alt="remove" />
            <form class="host-details-form-close" method="POST">
                <input type="image"  class="action-icon remove" name="close_host_details" src="tpl/<?= $cfg['theme'] ?>/img/close.png" alt="close" />
            </form>
        </div> <!--host-controls -->
        <?php if (!empty($tdata['host_details']['access_method'])) { ?>
            <div id="progress_bars">
                <?php
                if (!empty($tdata['host_details']['loadavg'][1])) {
                    $loadavg = 100 * $tdata['host_details']['loadavg'][1];
                    $max_load = 100 * $tdata['host_details']['ncpu'];
                    ?>
                    <label for="load_avg"><?= $lng['L_LOAD'] ?>:</label>
                    <progress id="load_avg" value="<?= $loadavg ?>" max="<?= $max_load ?>"  data-label="<?= $loadavg ?>"></progress>
                <?php } ?>
                <?php
                if (!empty($tdata['host_details']['mem'])) {
                    $mem = $tdata['host_details']['mem'];
                    ?>
                    <label for="mem"><?= $lng['L_MEM'] ?>:</label>
                    <progress id="mem" value="<?= $mem['mem_used'] ?>" max="<?= $mem['mem_available'] ?>"></progress>
                <?php } ?>
                <?php
                if (!empty($tdata['host_details']['disks']) && count($tdata['host_details']['disks']) > 0) {
                    foreach ($tdata['host_details']['disks'] as $disk) {
                        ?>
                        <label class="disk"><?= $disk['mounted'] ?>:</label>
                        <progress class="disk" value="<?= $disk['used_percent'] ?>" max="100"></progress>
                        <?php
                    }
                }
                ?>
            </div> <!-- progress container -->
        <?php } ?>
        <div class="">
            <div class="" >
                <label class="created_label"><?= $lng['L_ADDED'] ?></label>
                <span class="created"><?= $tdata['host_details']['formated_creation_date'] ?></span>
            </div>
            <?php if (!empty($tdata['host_details']['uptime']) && is_array($tdata['host_details']['uptime'])) { ?>
                <div class="" >
                    <label class="uptime_label"><?= $lng['L_UPTIME'] ?></label>
                    <span class="uptime"><?= formated_date($tdata['host_details']['uptime']['datetime']) ?></span>
                </div>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['latency_ms'])) { ?>
                <div class="" >
                    <label class="latency"><?= $lng['L_LATENCY'] ?></label>
                    <span class="latency"><?= $tdata['host_details']['latency_ms'] ?></span>
                </div>
            <?php } ?>

            <?php if (empty($tdata['host_details']['online']) && !empty($tdata['host_details']['f_last_seen'])) { ?>
                <div>
                    <label class="last_seen_label"><?= $lng['L_LAST_SEEN'] ?></label>
                    <span class="connected_date"><?= $tdata['host_details']['f_last_seen'] ?> </span>
                </div>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['f_last_check'])) { ?>
                <div>
                    <label class="connected_label"><?= $lng['L_LAST_CHECK'] ?>:</label>
                    <span class="connected_date"><?= $tdata['host_details']['f_last_check'] ?> </span>
                </div>
            <?php } ?>
        </div>
        <!--
        <?php if (!empty($tdata['host_details']['access_method'])) { ?>
                            <div class="charts">
                                <label class="none_opt"><?= $lng['L_NONE'] ?></label>
                                <input type="radio" checked name="graph_choice" value="none_graph">
                                <label class="network_opt">Network</label>
                                <input type="radio" name="graph_choice" value="network_graph">
                                <label class="ping_opt">Ping</label>
                                <input type="radio" name="graph_choice" value="ping_graph">
                                <label class="logs_opt">Logs</label>
                                <input type="radio" name="graph_choice" value="show_logs">
                            </div>
        <?php } ?>
        -->
        <!-- DEPLOYS -->
        <!--
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
        <?php
        if (!empty($tdata['host_details']['tail_syslog']) && valid_array($tdata['host_details']['tail_syslog'])) {
            ?><div class="log_container">
            <?php
            $logs = array_reverse($tdata['host_details']['tail_syslog']); //TODO move to backend not frontend
            foreach ($logs as $log) {
                ?>
                                            <div class="log_line"><?= $log ?></div>
            <?php }
            ?>
                            </div>
        <?php }
        ?>
        -->
        <!--
              <div class="ping_chart">
            <img alt="" src="tpl/default/img/graph.png"/>
        </div>
        -->
    </div> <!-- host-details-container -->
</div> <!-- host-details -->