<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
<div id="tab1" class="host-details-tab-content">
    <!-- PORTS -->
    <?php if (!empty($tdata['host_details']['agent_installed'])) : ?>
        <div class="ports_control">
            <label for="display_ipv6">IPV6</label>
            <input type="checkbox" id="display_ipv6" value="0">
            <label for="display_local">Local Ports</label>
            <input type="checkbox" id="display_local" value="0">
        </div>
    <?php endif; ?>
    <?php
    if (!empty($tdata['host_details']['remote_ports'])) {
        ?>
        <div class="host_port_container">
            <?php
            foreach ($tdata['host_details']['remote_ports'] as $port) {
                ?>
                <div class="port_container">
                    <?php if ($port['online']) : ?>
                        <img class="port-online" src="tpl/<?= $tdata['theme'] ?>/img/green2.png" alt=""/>
                    <?php else : ?>
                        <img class="port-offline" src="tpl/<?= $tdata['theme'] ?>/img/red2.png" alt=""/>
                    <?php endif; ?>
                    <!-- <div class="host_port_name"></div> -->
                    <div class="host_port">
                        (<?= $port['pnumber'] ?>)
                    </div>
                </div> <!-- port container -->
            <?php } ?>
        </div> <!-- host port container -->
    <?php } ?>
    <?php
    if (!empty($tdata['host_details']['agent_ports'])) :
        ?>
        <div class="host_port_container">
            <?php
            foreach ($tdata['host_details']['agent_ports'] as $port) :
                $port['protocol'] = $port['protocol'] == 1 ? 'TCP' : 'UDP';
                $port_name = !empty($port['custom_service']) ? $port['custom_service'] : $port['service'];
                ?>
                <div class="port_container <?= !empty($port['class']) ? $port['class'] : null ?> "
                    data-tooltip="<?= $port['pnumber'] .
                        '/' . $port['protocol'] .
                        ' ' . $port['ip_version'] .
                        ' ' . $port['interface']
                        ?>
                ">
                    <?php if ($port['online']) : ?>
                        <img class="port-online" src="tpl/<?= $tdata['theme'] ?>/img/green2.png" alt=""/>
                    <?php else : ?>
                        <img class="port-offline" src="tpl/<?= $tdata['theme'] ?>/img/red2.png" alt=""/>
                    <?php endif; ?>
                    <!-- <div class="host_port_name"></div> -->
                    <div class="host_port">
                        <div class="port_status">
                            <?=  $port_name  ?>
                        </div>
                    </div>
                </div> <!-- port container -->
            <?php endforeach; ?>
        </div> <!-- host port container -->
    <?php endif; ?>

    <!-- END PORTS -->
    <div class="resume_container">
        <div class="resume-left-column">
            <!-- LEFT -->
            <?php if (!empty($tdata['host_details']['agent_installed'])) : ?>
                <button id="auto_reload_host_details">Auto: OFF</button>
            <?php endif; ?>

            <div class="resume-section">
                <table class="resume-fields-table">
                    <tr>
                        <td class="resume_label"><?= $lng['L_NAME'] ?>:</td>
                        <td class="display_name"><?= $tdata['host_details']['display_name'] ?></td>
                    </tr>
                    <tr>
                        <td class="resume_label"><?= $lng['L_IP'] ?>:</td>
                        <td class="display_name"><?= $tdata['host_details']['ip'] ?></td>
                    </tr>
                    <tr>
                        <td class="resume_label"><?= $lng['L_HOSTNAME'] ?>:</td>
                        <td class="display_name"><?= $tdata['host_details']['hostname'] ?></td>
                    </tr>
                    <tr>
                        <td class="resume_label"><?= $lng['L_NETWORK'] ?>:</td>
                        <td><?= $tdata['host_details']['net_cidr'] ?></td>
                    </tr>
                    <tr>
                        <td class="resume_label"><?= $lng['L_NETWORK_NAME'] ?>:</td>
                        <td><?= $tdata['host_details']['network_name'] ?></td>
                    </tr>
                    <tr>
                        <td class="resume_label"><?= $lng['L_VLAN'] ?>:</td>
                        <td><?= $tdata['host_details']['network_vlan'] ?></td>
                    </tr>
                    <?php if (!empty($h_misc['owner'])) : ?>
                    <tr>
                        <td class="resume_label"><?= $lng['L_OWNER'] ?>:</td>
                        <td class="resume_field"><?= $h_misc['owner'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="resume_label"><?= $lng['L_ADDED'] ?>:</td>
                        <td class="resume_field"><?= $tdata['host_details']['formated_creation_date'] ?></td>
                    </tr>
                    <?php if (!empty($h_misc['uptime'])) : ?>
                    <tr>
                        <td class="resume_label"><?= $lng['L_UPTIME'] ?>:</td>
                        <td class="resume_field"><?= $h_misc['uptime']?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($tdata['host_details']['latency_ms'])) : ?>
                    <tr>
                        <td class="resume_label"><?= $lng['L_LATENCY'] ?>:</td>
                        <td class="resume_field"><?= $tdata['host_details']['latency_ms'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($tdata['host_details']['ncpu'])) : ?>
                    <tr>
                        <td class="resume_label">CPUs:</td>
                        <td class="cpu_field"><?= $h_misc['ncpu'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($tdata['host_details']['f_last_check'])) : ?>
                    <tr>
                        <td class="resume_label"><?= $lng['L_LAST_PING_CHECK'] ?>:</td>
                        <td class="resume_field"><?= $tdata['host_details']['f_last_check'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($tdata['host_details']['f_last_seen'])) : ?>
                    <tr>
                        <td class="resume_label"><?= $lng['L_LAST_SEEN_ONLINE'] ?>:</td>
                        <td class="resume_field"><?= $tdata['host_details']['f_last_seen'] ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <?php if (!empty($h_misc['f_agent_contact'])) : ?>
            <div class="resume-section">
                <table class="resume-fields-table">
                    <tr>
                        <td class="resume_label"><?= $lng['L_AGENT_INSTALLED'] ?>:</td>
                        <td class="resume_field">
                            <?= $h_misc['agent_version'] ?>
                            (<?= $ncfg->get('agent_latest_version') ?>)
                        </td>
                    </tr>
                    <tr>
                        <td class="resume_label"><?= $lng['L_AGENT_LAST_PING'] ?>:</td>
                        <td class="resume_field"><?= $h_misc['f_agent_contact'] ?></td>
                    </tr>
                </table>
            </div>
            <?php endif; ?>

            <?php if (!empty($h_misc['access_link'])) : ?>
            <div class="resume-section">
                <a href="<?= $h_misc['access_link'] ?>" target="_blank"><?= $h_misc['access_link'] ?></a>
            </div>
            <?php endif; ?>
        </div>
        <!-- RIGHT COLUMN -->
        <div class="resume-right-column">
            <?php
            if (
                !empty($tdata['host_details']['load_avg']) &&
                is_string($tdata['host_details']['load_avg'])
            ) :
                ?>
            <div id="load_container" class="load_container">
                <?php
                    print $tdata['host_details']['load_avg'];
                ?>
            </div>
                <?php
            endif;

            if (!empty($tdata['host_details']['iowait_stats'])) :
                ?>
            <div id="iowait_container" class="iowait_container">
                <?php
                    print $tdata['host_details']['iowait_stats'];
                ?>
                </div>
                <?php
            endif;
            ?>
            <div id="bars_container" class="bars_container">
                <?php
                if (
                    !empty($tdata['host_details']['mem_info']) &&
                    is_string($tdata['host_details']['mem_info'])
                ) :
                    print $tdata['host_details']['mem_info'];
                endif;

                if (
                    !empty($tdata['host_details']['disks_info']) &&
                    is_string($tdata['host_details']['disks_info'])
                ) :
                    print $tdata['host_details']['disks_info'];
                endif;
                ?>
            </div>
        </div>
    </div>
</div>
