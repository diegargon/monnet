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
        <div class="host-details-bar">
            <div class="host-controls-left">
                <form class="host-details-form-close" method="POST">
                    <input type="image"  class="action-icon remove" name="close_host_details" src="tpl/<?= $cfg['theme'] ?>/img/close.png" alt="<?= $lng['L_CLOSE'] ?>" title="<?= $lng['L_CLOSE'] ?>" />
                </form>
                <!--
                <form class="host-details-form-options" method="POST">
                    <select class="host-details-select" name="host-details-select">
                        <option value="1"><?= $lng['L_OVERVIEW'] ?></option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </form>
                -->
                <div class="host-details-tabs-head-container">
                    <button id="tab1_btn" class="host-details-tabs-head" onclick="changeTab('tab1')"><?= $lng['L_OVERVIEW'] ?></button>
                    <button id="tab2_btn" class="host-details-tabs-head" onclick="changeTab('tab2')"><?= $lng['L_STATUS'] ?></button>
                    <button id="tab3_btn" class="host-details-tabs-head" onclick="changeTab('tab3')"><?= $lng['L_NOTES'] ?></button>
                    <?php if (!empty($tdata['host_details']['ping_graph'])) { ?>
                        <button id="tab4_btn" class="host-details-tabs-head" onclick="changeTab('tab4')"><?= $lng['L_METRICS'] ?></button>
                    <?php } ?>
                    <!-- 
                    <button id="tabx_btn" class="host-details-tabs-head" onclick="changeTab('tab2')"><?= $lng['L_DEPLOYS'] ?></button>
                    -->
                </div>
            </div> <!--host-controls-right -->            
            <div class="host-controls-right">
                <?php if (!empty($tdata['host_details']['mac']) && empty($tdata['host_details']['online'])) { ?>
                    <input onClick="refresh('power_on', <?= $tdata['host_details']['id'] ?>)" type="image" class="action-icon power-off" src="tpl/<?= $cfg['theme'] ?>/img/power-off.png" alt="<?= $lng['L_PWR_ON'] ?>" title="<?= $lng['L_PWR_ON'] ?>"/>
                <?php } ?>
                <?php if (!empty($tdata['host_details']['access_method']) && !empty($tdata['host_details']['online'])) { ?>
                    <input onClick="refresh('power_off', <?= $tdata['host_details']['id'] ?>)" type="image" class="action-icon power-on" src="tpl/<?= $cfg['theme'] ?>/img/power-on.png" alt="<?= $lng['L_PWR_OFF'] ?>" title="<?= $lng['L_PWR_OFF'] ?>"/>
                <?php } ?>
                <?php if (!empty($tdata['host_details']['access_method'])) { ?>
                    <input onClick="refresh('reboot', <?= $tdata['host_details']['id'] ?>)" type="image" class="action-icon reboot" src="tpl/<?= $cfg['theme'] ?>/img/reboot.png" alt="<?= $lng['L_REBOOT'] ?>" title="<?= $lng['L_REBOOT'] ?>"/>
                <?php } ?>
                <input onClick="refresh('remove_host', <?= $tdata['host_details']['id'] ?>)" type="image"  class="action-icon remove" src="tpl/<?= $cfg['theme'] ?>/img/remove.png" alt="<?= $lng['L_DELETE'] ?>" title="<?= $lng['L_DELETE'] ?>"/>
            </div> <!--host-controls-right -->

        </div>
        <div class="host-details-main">           
            <img class="hosts-item" src="<?= $tdata['host_details']['online_image'] ?>" alt=="<?= $tdata['host_details']['title_online'] ?>" title="<?= $tdata['host_details']['title_online'] ?>"/>
            <?php if (!empty($tdata['host_details']['os_image'])) { ?>
                <img class="fab" src="<?= $tdata['host_details']['os_image'] ?>" alt="<?= $tdata['host_details']['os_name'] ?>" title="<?= $tdata['host_details']['os_name'] ?>"/>
            <?php } ?>

            <?php if (!empty($tdata['host_details']['system_image'])) { ?>
                <img class="fab" src="<?= $tdata['host_details']['system_image'] ?>" alt="<?= $tdata['host_details']['system_name'] ?>" title="<?= $tdata['host_details']['system_name'] ?>"/>
            <?php } ?>
            <?php if (!empty($tdata['host_details']['os_distribution_image'])) { ?>
                <img class="fab" src="<?= $tdata['host_details']['os_distribution_image'] ?>" alt="<?= $tdata['host_details']['os_distribution_name'] ?>" title="<?= $tdata['host_details']['os_distribution_name'] ?>"/>
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
        <div id="tab1" class="host-details-tab-content">
            <div class="">
                <div class="" >
                    <label class="created_label"><?= $lng['L_ADDED'] ?></label>
                    <span class="created"><?= $tdata['host_details']['formated_creation_date'] ?></span>
                </div>
                <?php if (!empty($tdata['host_details']['uptime']) && is_array($tdata['host_details']['uptime'])) { ?>
                    <div class="" >
                        <label class="uptime_label"><?= $lng['L_UPTIME'] ?></label>
                        <span class="uptime"><?= $tdata['host_details']['uptime']['datetime'] ?></span>
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
        </div>
        <div id="tab2" class="host-details-tab-content">
            <?php if (!empty($tdata['host_details']['access_method'])) { ?>
                <div id="progress_bars">
                    <?php
                    if (!empty($tdata['host_details']['loadavg'][1]) && is_numeric($tdata['host_details']['loadavg'][1])) {
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
        </div>

        <div id="tab3" class="host-details-tab-content">
            <div class="textarea-bar">

            </div>
            <textarea id="textnotes" name="textnotes" rows="10" cols="100">
                <?= $tdata['host_details']['notes'] ?>
            </textarea>

        </div>
        <div id="tab4" class="host-details-tab-content">
            <div class="ping_graph_container">
                <?= $tdata['host_details']['ping_graph'] ?>
            </div>

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