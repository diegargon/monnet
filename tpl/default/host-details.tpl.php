<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var Config $ncfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
$h_misc = $tdata['host_details']['misc'];
?>

<div id="host-details" class="host-details draggable" style="display:block">
    <div id="host-details-container" class="host-details-container">
        <!-- HOST COMMON BAR -->
        <!-- FIRST HEADED BAR -->
        <div class="host-details-bar dragbar">
            <div class="host-controls-left">
                <button id="close_host_details" type="submit"  class="button-ctrl">
                    <img src="tpl/<?= $ncfg->get('theme') ?>/img/close.png"
                        alt="<?= $lng['L_CLOSE'] ?>" title="<?= $lng['L_CLOSE'] ?>" />
                </button>
                <button id="max_host_details" type="submit"  class="button-ctrl">
                    <img src="tpl/<?= $ncfg->get('theme') ?>/img/maximize.png"
                        alt="<?= $lng['L_MAXIMIZE'] ?>" title="<?= $lng['L_MAXIMIZE'] ?>" />
                </button>
                <div class="host-details-tabs-head-container">
                    <button id="tab1_btn" class="host-details-tabs-head" data-tab="1"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab1')">
                            <?= $lng['L_OVERVIEW'] ?>
                    </button>
                    <button id="tab3_btn" class="host-details-tabs-head" data-tab="3"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab3')">
                            <?= $lng['L_NOTES'] ?>
                    </button>
                        <button id="tab9_btn" class="host-details-tabs-head" data-tab="9"
                                onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab9')">
                                <?= $lng['L_LOG'] ?>
                        </button>
                    <button id="tab10_btn" class="host-details-tabs-head" data-tab="10"
                            onclick="changeHDTab(<?= $tdata['host_details']['id'] ?>, 'tab10')">
                            <?= $lng['L_METRICS'] ?>
                    </button>

                    <button id="tab11_btn" class="host-details-tabs-head" data-tab="11"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab11')">
                        <?= $lng['L_ALARMS'] ?>
                    </button>
                    <button id="tab12_btn" class="host-details-tabs-head" data-tab="12"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab12')">
                        <?= $lng['L_CONFIG'] ?>
                    </button>
                <?php
                if ($tdata['host_details']['agent_installed']) :
                ?>
                    <button id="tab16_btn" class="host-details-tabs-head" data-tab="16"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab16')">
                        <?= $lng['L_AGENT'] ?>
                    </button>
                <?php
                endif;
                ?>
                    <button id="tab13_btn" class="host-details-tabs-head" data-tab="13"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab13')">
                        <?= $lng['L_CHECKS'] ?>
                    </button>
                    <?php if (!empty($ncfg->get('ansible')) && !empty($tdata['host_details']['ansible_enabled'])) : ?>
                    <button id="tab15_btn" class="host-details-tabs-head" data-tab="15"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab15')">
                            <?= $lng['L_TASKS'] ?>
                    </button>
                    <button id="tab20_btn" class="host-details-tabs-head" data-tab="20"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab20')">
                            Ansible
                    </button>
                    <?php endif; ?>
                </div>
            </div> <!--host-controls-right -->
            <div class="host-controls-right">
                <?php if (!empty($tdata['host_details']['mac']) && empty($tdata['host_details']['online'])) : ?>
                    <input onClick="submitCommand('power_on', {id: <?= $tdata['host_details']['id'] ?>})" type="image"
                        class="action-icon power-off" src="tpl/<?= $ncfg->get('theme') ?>/img/power-off.png"
                        alt="<?= $lng['L_PWR_ON'] ?>" title="<?= $lng['L_PWR_ON'] ?>"/>
                <?php endif; ?>
                <?php
                if (
                    !empty($tdata['host_details']['ansible_enabled']) &&
                    !empty($tdata['host_details']['online'])
                ) {
                    ?>
                    <input onClick="submitCommand('shutdown', {id:<?= $tdata['host_details']['id'] ?>})" type="image"
                        class="action-icon power-on" src="tpl/<?= $ncfg->get('theme') ?>/img/power-on.png"
                        alt="<?= $lng['L_PWR_OFF'] ?>" title="<?= $lng['L_PWR_OFF'] ?>"/>
                <?php } ?>
                <?php if (!empty($tdata['host_details']['ansible_enabled'])) : ?>
                <input onClick="submitCommand('reboot', {id:<?= $tdata['host_details']['id'] ?>})" type="image"
                    class="action-icon reboot" src="tpl/<?= $ncfg->get('theme') ?>/img/reboot.png"
                    alt="<?= $lng['L_REBOOT'] ?>" title="<?= $lng['L_REBOOT'] ?>"/>
                <?php endif; ?>
                <input onClick="confirmSubmit('remove_host',{id:<?= $tdata['host_details']['id']?>})" type="image"
                    class="action-icon remove" src="tpl/<?= $ncfg->get('theme') ?>/img/remove.png"
                    alt="<?= $lng['L_DELETE'] ?>" title="<?= $lng['L_DELETE'] ?>"/>
            </div> <!--host-controls-right -->

        </div>
        <!-- SECOND HEADED BAR -->
        <div class="host-details-main">
            <img class="hosts-item" src="<?= $tdata['host_details']['online_image'] ?>"
                alt=="<?= $tdata['host_details']['title_online'] ?>"
                title="<?= $tdata['host_details']['title_online'] ?>"/>
            <?php if (!empty($tdata['host_details']['manufacture_image'])) : ?>
                <img class="fab" src="<?= $tdata['host_details']['manufacture_image'] ?>"
                    alt="<?= $tdata['host_details']['manufacture_name'] ?>"
                    title="<?= $tdata['host_details']['manufacture_name'] ?>"/>
            <?php endif; ?>

            <?php if (!empty($tdata['host_details']['os_image'])) : ?>
                <img class="fab" src="<?= $tdata['host_details']['os_image'] ?>"
                    alt="<?= $tdata['host_details']['os_name'] ?>"
                    title="<?= $tdata['host_details']['os_name'] ?>"/>
            <?php endif; ?>

            <?php if (!empty($tdata['host_details']['system_rol_image'])) : ?>
                <img class="fab" src="<?= $tdata['host_details']['system_rol_image'] ?>"
                    alt="<?= $tdata['host_details']['system_rol_name'] ?>"
                    title="<?= $tdata['host_details']['system_rol_name'] ?>"/>
            <?php endif; ?>
            <?php if (!empty($ncfg->get('ansible')) && !empty($tdata['host_details']['ansible_enabled'])) : ?>
                <img class="fab" src="tpl/<?= $tdata['theme']?>/img/ansible.png"
                    alt="ansible" title="ansible"/>
            <?php endif; ?>
            <?php
            if (!empty($h_misc['machine_type']) && (int) $h_misc['machine_type'] === 2) :
                ?>
                <img class="fab" src="tpl/<?= $tdata['theme']?>/img/vm.png"
                    alt="vm" title="vm"/>
            <?php endif; ?>
            <div class="host-item"><?= $tdata['host_details']['title'] ?> </div>
            <?php if (!empty($tdata['host_details']['hostname'])) : ?>
                <div class="host-item"><?= $tdata['host_details']['hostname'] ?> </div>
            <?php endif; ?>

        </div> <!-- host-details-main -->
        <div class="host-details-main">
            <div class="host-item">id <?= $tdata['host_details']['id'] ?></div>
            <div class="host-item"><?= $tdata['host_details']['ip'] ?></div>
            <?php if (!empty($tdata['host_details']['mac'])) : ?>
                <div class="host-item"><?= $tdata['host_details']['mac'] ?> </div>
            <?php endif; ?>
            <?php
            if (
                !empty($h_misc['mac_vendor']) &&
                $h_misc['mac_vendor'] != '-'
            ) {
                ?>
                <div class="host-item"><?= $h_misc['mac_vendor'] ?> </div>
            <?php } ?>
            <div class="status_msg"></div>
        </div>

        <!-- /HOST COMMON BAR -->
        <!-- TAB1  RESUME -->
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
                    <div class="">
                        <span class="resume_label"><?= $lng['L_NAME'] ?>:</span>
                        <span class="display_name"><?= $tdata['host_details']['display_name'] ?></span>
                    </div>
                    <div class="">
                        <span class="resume_label"><?= $lng['L_IP'] ?>:</span>
                        <span class="display_name"><?= $tdata['host_details']['ip'] ?></span>
                    </div>
                    <div class="">
                        <span class="resume_label"><?= $lng['L_HOSTNAME'] ?>:</span>
                        <span class="display_name"><?= $tdata['host_details']['hostname'] ?></span>
                    </div>
                    <div class"">
                        <div><?= $lng['L_NETWORK'] ?>: <?= $tdata['host_details']['net_cidr'] ?></div>
                        <div><?= $lng['L_NETWORK_NAME'] ?>: <?= $tdata['host_details']['network_name'] ?></div>
                        <div><?= $lng['L_VLAN'] ?>: <?= $tdata['host_details']['network_vlan'] ?></div>
                    </div>
                    <?php if (!empty($h_misc['owner'])) : ?>
                        <div class="">
                            <span class="resume_label"><?= $lng['L_OWNER'] ?>:</span>
                            <span class="resume_field"><?= $h_misc['owner'] ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="">
                        <span class="resume_label"><?= $lng['L_ADDED'] ?>:</span>
                        <span class="resume_field">
                            <?= $tdata['host_details']['formated_creation_date'] ?>
                        </span>
                    </div>
                    <?php
                    if (
                        !empty($h_misc['uptime'])
                    ) {
                        ?>
                        <div class="" >
                            <span class="resume_label"><?= $lng['L_UPTIME'] ?>:</span>
                            <span class="resume_field">
                                <?= $h_misc['uptime']?>
                            </span>
                        </div>
                    <?php } ?>
                    <?php if (!empty($tdata['host_details']['latency_ms'])) : ?>
                        <span class="resume_label"><?= $lng['L_LATENCY'] ?>:</span>
                        <span class="resume_field"><?= $tdata['host_details']['latency_ms'] ?></span>
                    <?php endif; ?>

                    <?php
                    if (!empty($tdata['host_details']['ncpu'])) {
                        ?>
                        <div>
                            <span class="cpu_label">CPUs: </span>
                            <span class="cpu_field"><?= $h_misc['ncpu'] ?></span>
                        </div>
                    <?php } ?>

                    <?php if (!empty($tdata['host_details']['f_last_check'])) : ?>
                        <div>
                            <span class="resume_label"><?= $lng['L_LAST_PING_CHECK'] ?>: </span>
                            <span class="resume_field"><?= $tdata['host_details']['f_last_check'] ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($h_misc['agent_last_contact'])) : ?>
                        <div>
                            <span class="resume_field"><?= $lng['L_AGENT_INSTALLED'] ?></span>
                            <span class="resume_field">
                                <?= $h_misc['agent_version'] ?>
                                (<?= $ncfg->get('agent_latest_version') ?>)
                            </span>
                        </div>
                        <div>
                            <span class="resume_label"><?= $lng['L_AGENT_LAST_PING'] ?>:</span>
                            <span class="resume_field"><?= $h_misc['agent_last_contact'] ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($h_misc['access_link'])) : ?>
                        <div>
                            <a href="<?= $h_misc['access_link'] ?>"
                                target="_blank"><?= $h_misc['access_link'] ?>
                            </a>
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
        <!-- /TAB1 RESUME-->
        <!-- TAB3 --><!-- NOTES -->
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
        <!-- /TAB3 -->
        <!-- TAB9 -->
        <!-- HOST LOGS -->
        <div id="tab9" class="host-details-tab-content">
            <div class="logs_header">
                <div>
                    <button id="logs_reload_btn">Reload</button>
                    <button id="auto_reload_logs">Auto: OFF</button>
                </div>
                <div>
                    <select id="log_level" name="log_level" data-btn="auto_reload_logs">
                        <option value="7">LOG_DEBUG</option>
                        <option value="6">LOG_INFO</option>
                        <option value="5">LOG_NOTICE</option>
                        <option value="4">LOG_WARNING</option>
                        <option value="3">LOG_ERROR</option>
                        <option value="2">LOG_CRITICAL</option>
                        <option value="1">LOG_ALERT</option>
                        <option value="0">LOG_EMERGENCY</option>
                    </select>
                </div>
            </div>
            <label for="log_size">Nº:</label>
            <input type="number" id="log_size" name="log_size" step="25" value="25" data-btn="auto_reload_logs">
        <?php
        if (!empty($ncfg->get('ansible')) && !empty($tdata['host_details']['ansible_enabled'])) :
        ?>
            <div class="inline"><button id="syslog_btn">Syslog</button></div>
            <div class="inline"><button id="journald_btn">Journald</button></div>
        <?php
        endif;
        if (!empty($tdata['host_details']['host_logs'])) :
            echo $tdata['host_details']['host_logs'];
        endif;
        ?>
        </div>
        <!-- /TAB9 -->
        <!-- TAB10 --><!-- Graphs / PING -->
        <div id="tab10" class="host-details-tab-content">
            <div id="graphs_container" class="graphs_container">
                <?= !empty($tdata['host_details']['ping_graph']) ? $tdata['host_details']['ping_graph'] : null ?>
            </div>
        </div>
        <!-- /TAB10 -->
        <!-- TAB11 ALARMS -->
        <div id="tab11" class="host-details-tab-content">
            <div class="alarms_container">
                <div class="">
                    <label for="disableAlarms"><?= $lng['L_DISABLE_ALL_ALARMS'] ?>:</label>
                    <input type="hidden" id="disableAlarms" value="0">
                    <input
                        onchange="submitCommand('setHostAlarms',
                            {id: <?= $tdata['host_details']['id']?>, value: this.checked})"
                            type="checkbox" id="disableAlarms"
                            <?= !empty($h_misc['disable_alarms']) ? 'checked' : null ?>>
                    <div><?= $lng['L_DISABLE_PER_TYPE']?></div>
                    <label for=""><?= $lng['L_ALARM_PING']?>:</label>
                    <input type="hidden" id="alarm_ping_disable" value="0">
                    <input type="checkbox"
                        id="alarm_ping_disable"
                        data-command="alarm_ping_disable"
                        <?= !empty($h_misc['alarm_ping_disable']) ? 'checked' : null ?>
                        >
                    <label for=""><?= $lng['L_ALARM_PING_PORT']?>:</label>
                    <input type="hidden" id="alarm_port_disable" value="0">
                    <input type="checkbox"
                        id="alarm_port_disable"
                        data-command="alarm_port_disable"
                        <?= !empty($h_misc['alarm_port_disable']) ? 'checked' : null ?>
                        >
                    <label for=""><?= $lng['L_ALARM_MACCHANGE']?>:</label>
                    <input type="hidden" id="alarm_macchange_disable" value="0">
                    <input type="checkbox"
                        id="alarm_macchange_disable"
                        data-command="alarm_macchange_disable""
                        <?= !empty($h_misc['alarm_macchange_disable']) ? 'checked' : null ?>
                        >
                    <label for=""><?= $lng['L_ALARM_NEW_PORT']?>:</label>
                    <input disable type="hidden" id="alarm_newport_disable" value="0">
                    <input disable type="checkbox"
                        id="alarm_newport_disable"
                        data-command="alarm_newport_disable"
                        <?= !empty($h_misc['alarm_newport_disable']) ? 'checked' : null ?>
                        >
                    <label for=""><?= $lng['L_ALARM_HOSTNAME']?>:</label>
                    <input disable type="hidden" id="alarm_hostname_disable" value="0">
                    <input disable type="checkbox"
                        id="alarm_hostname_disable"
                        data-command="alarm_hostname_disable"
                        <?= !empty($h_misc['alarm_hostname_disable']) ? 'checked' : null ?>
                    >
                    <br/>
                    <label for="enableEmailAlarms"><?= $lng['L_EMAIL_ALARMS'] ?>:</label>
                    <input
                        type="checkbox" id="toggleMailAlarms"
                        data-command="toggleMailAlarms"
                        <?= !empty($h_misc['email_alarms']) ? 'checked' : null ?>
                    >
                    <br/>
                    <div><?= $lng['L_ENABLE_PER_TYPE']?></div>
                    <label for=""><?= $lng['L_ALARM_PING']?>:</label>
                    <input type="hidden" id="alarm_ping_email" value="0">
                    <input type="checkbox"
                        id="alarm_ping_email"
                        data-command="alarm_ping_email"
                        <?= !empty($h_misc['alarm_ping_email']) ? 'checked' : null ?>
                    >
                    <label for=""><?= $lng['L_ALARM_PING_PORT']?>:</label>
                    <input type="hidden" id="alarm_port_email" value="0">
                    <input type="checkbox"
                        id="alarm_port_email"
                        data-command="alarm_port_email"
                        <?= !empty($h_misc['alarm_port_email']) ? 'checked' : null ?>
                        >
                    <label for=""><?= $lng['L_ALARM_MACCHANGE']?>:</label>
                    <input type="hidden" id="alarm_macchange_email" value="0">
                    <input type="checkbox"
                        id="alarm_macchange_email"
                        data-command="alarm_macchange_email"
                        <?= !empty($h_misc['alarm_macchange_email']) ? 'checked' : null ?>
                    >
                    <label for=""><?= $lng['L_ALARM_NEW_PORT']?>:</label>
                    <input disable type="hidden" id="alarm_newport_email" value="0">
                    <input disable type="checkbox"
                        id="alarm_newport_email"
                        data-command="alarm_newport_email"
                        <?= !empty($h_misc['alarm_newport_email']) ? 'checked' : null ?>
                    >
                    <br/>
                    <label for="alarm_emails">Emails (Comma Separated)</label><br/>
                    <input type="text"
                        size="50"
                        id="alarm_emails"
                        placeholder="Enter emails separated by commas"
                        value="<?php
                            if (!empty($h_misc['email_list'])) {
                                echo $h_misc['email_list'];
                            }
                            ?>"
                    >
                    <div id="email_feedback" style="color: red; font-size: 0.9em;"></div>
                </div>
                <div>
                    <button id="clear_host_alarms"
                        onclick="submitCommand('clearHostAlarms',{id: <?= $tdata['host_details']['id'] ?>})">
                        <?= $lng['L_CLEAR_ALARMS_BITS'] ?>
                    </button>
                </div>
            </div>
        </div>
        <!-- /TAB11 -->
        <!-- /TAB12 --><!-- Config -->
        <div id="tab12" class="host-details-tab-content">
            <div id="config_status_msg"></div>
            <div class="config_container">
                <div class="left-config-column">
                    <div class="">
                        <label for="chkHighlight"><?= $lng['L_HIGHLIGHT_HOSTS'] ?>:</label>
                        <input type="checkbox"
                            id="chkHighlight" <?= $tdata['host_details']['highlight'] ? 'checked' : null ?>>
                        <input type="number" id="host_id" name="host_id"
                            style="display:none;" readonly value="<?= $tdata['host_details']['id'] ?>"/>
                    </div>
                    <?php if ($ncfg->get('ansible')) : ?>
                    <div class="">
                        <label for="ansible_enabled"><?= $lng['L_ANSIBLE_SUPPORT'] ?></label>
                        <input type="hidden" id="ansible_enabled" value="0">
                        <input
                            type="checkbox"
                            id="ansible_enabled"
                            <?= !empty($tdata['host_details']['ansible_enabled']) ? ' checked' : '' ?>>
                    </div>
                    <?php endif; ?>
                    <div class="">
                        <label for="always_on"><?= $lng['L_ALWAYS_ON'] ?></label>
                        <input type="hidden" id="always_on" value="0">
                        <input
                            type="checkbox"
                            id="always_on"
                            data-command="setAlwaysOn"
                            <?= !empty($h_misc['always_on']) ? ' checked' : '' ?>>
                    </div>
                    <div class="">
                        <label for="disable_host"><?= $lng['L_DISABLE'] ?></label>
                        <input type="hidden" id="host_disable" value="0">
                        <input
                            type="checkbox"
                            id="host_on"
                            data-command="setHostDisable"
                            <?= !empty($tdata['host_details']['disable']) ? ' checked' : '' ?>>
                    </div>
                    <div class="">
                        <label for="host-title"><?= $lng['L_DISPLAY_NAME'] ?></label><br />
                        <input type="text" id="host-title" size="12" max-size="15" name="host-title"
                            value="<?= $tdata['host_details']['title'] ?>"/>
                        <button id="submitTitle"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="host-name"><?= $lng['L_HOSTNAME'] ?></label><br />
                        <input type="text" id="host-name" size="30" max-size="40" name="host-title"
                            value="<?= $tdata['host_details']['hostname'] ?>"/>
                        <button id="submitHostname"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="host-cat"><?= $lng['L_CATEGORY'] ?></label><br />
                        <select id="hostcat_id" name="hostcat_id">
                            <?php foreach ($tdata['host_details']['hosts_categories'] as $cat) : ?>
                                <?php
                                $cat_name = isset($lng[$cat['cat_name']]) ? $lng[$cat['cat_name']] : $cat['cat_name'];
                                $selected = $cat['id'] == $tdata['host_details']['category'] ? ' selected=1 ' : '';
                                ?>
                                <option value="<?= $cat['id'] ?>"<?= $selected ?>><?= $cat_name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button id="submitChangeCat"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="host_owner"><?= $lng['L_OWNER'] ?>: </label><br />
                        <input
                            type="text" id="host_owner" name="host_owner"
                            value="<?=
                                !empty($h_misc['owner'])
                                ? $h_misc['owner']
                                : null
                            ?>"
                        />
                        <button id="submitOwner"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="access_link"><?= $lng['L_ACCESS'] ?>: </label><br />
                        <input
                            type="text"
                            id="access_link"
                            name="access_link"
                            value="<?=
                                !empty($h_misc['access_link'])
                                ? $h_misc['access_link']
                                : null ?>"
                        />
                        <select id="access_link_type" name="access_link_type">
                            <?php foreach ($ncfg->get('access_link_types') as $key => $access_type) : ?>
                                <option value="<?= $key ?>" selected="1"><?= $access_type ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button id="submitAccessLink"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="host_token"><?= $lng['L_TOKEN'] ?>: </label><br/>
                        <input type="text" size="32" id="host_token" name="host_token"
                            value="<?= $tdata['host_details']['token'] ?>" readonly/>
                        <button id="submitHostToken"><?= $lng['L_CREATE'] ?></button>
                    </div>
                </div>
                <!-- /left config column -->
                <!-- right config column -->
                <div class="right-config-column">
                    <div class="">
                        <label for="machine_type"><?= $lng['L_MACHINE_TYPE'] ?>: </label><br/>
                        <select id="machine_type">
                            <?php foreach ($ncfg->get('machine_type') as $mtype) :
                                $selected = '';
                                if (
                                    !empty($h_misc['machine_type']) &&
                                    ($mtype['id'] == $h_misc['machine_type'])
                                ) :
                                    $selected = ' selected=1 ';
                                endif; ?>
                                <option value="<?= $mtype['id'] ?>"<?= $selected ?>><?= $mtype['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button id="submitMachineType"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="manufacture"><?= $lng['L_PROVIDER'] ?>: </label><br/>
                        <select id="manufacture">
                            <?php foreach ($ncfg->get('manufacture') as $manufacture) :
                                $selected = '';
                                if (
                                    !empty($h_misc['manufacture']) &&
                                    ($manufacture['id'] == $h_misc['manufacture'])
                                ) :
                                    $selected = ' selected=1 ';
                                endif; ?>
                                <option value="<?= $manufacture['id'] ?>"<?= $selected ?>><?= $manufacture['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button id="submitManufacture"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="os"><?= $lng['L_OS'] ?>: </label><br/>
                        <select id="os">
                            <?php foreach ($ncfg->get('os') as $os) :
                                $selected = '';
                                if (
                                    !empty($h_misc['os']) &&
                                    ($os['id'] == $h_misc['os'])
                                ) :
                                    $selected = ' selected=1 ';
                                endif;
                                ?>
                                <option value="<?= $os['id'] ?>"<?= $selected ?>><?= $os['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button id="submitOS"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="system_rol"><?= $lng['L_ROL'] ?>: </label><br/>
                        <select id="system_rol">
                            <?php foreach ($ncfg->get('system_rol') as $system_rol) :
                                $selected = '';
                                if (
                                    !empty($h_misc['system_rol']) &&
                                    ($system_rol['id'] == $h_misc['system_rol'])
                                ) :
                                    $selected = ' selected=1 ';
                                endif;
                                ?>
                                <option value="<?= $system_rol['id'] ?>"<?= $selected ?>><?= $system_rol['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button id="submitSystemRol"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="system_aval"><?= $lng['L_AVAILABILITY'] ?>: </label><br/>
                        <select id="system_aval">
                            <?php foreach ($ncfg->get('sys_availability') as $sys_aval) :
                                $selected = '';
                                if (
                                    !empty($h_misc['sys_availability']) &&
                                    ($sys_aval['id'] == $h_misc['sys_availability'])
                                ) :
                                    $selected = ' selected=1 ';
                                endif;
                                ?>
                                <option value="<?= $sys_aval['id'] ?>"<?= $selected ?>><?= $sys_aval['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button id="submitSysAval"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="os_version"><?= $lng['L_VERSION'] ?>: </label><br/>
                        <input type="text" size="20" id="os_version" name="os_version"
                            value="<?= $h_misc['os_version'] ?? '' ?>" />
                        <button id="submitOSVersion"><?= $lng['L_SEND'] ?></button>
                    </div>
                </div>
                <!-- /right config column -->

            </div>
        </div>
        <!-- /TAB12 -->
        <!-- TAB13 --><!-- Checks -->
        <div id="tab13" class="host-details-tab-content">
            <div id="config_status_msg"></div>
            <div class="config_container">
                <div class="left-config-column">
                    <div class="">
                        <label for="disable_ping"><?= $lng['L_PING_CHECK_DISABLE'] ?>: </label>
                        <input type="hidden" id="disable_ping" value="0"/>
                        <input type="checkbox" id="disable_ping"
                            <?= !empty($h_misc['disable_ping']) ? 'checked' : null ?>><br />
                    </div>
                    <div class="">
                        <label for="checkport"><?= $lng['L_REMOTE_PORT_CHECK'] ?>: </label>
                        <input type="checkbox" id="checkports_enabled"
                            <?= $tdata['host_details']['check_method'] == 2 ? 'checked' : null ?>><br />
                        <input type="number" id="port_number" name="port_number" size="5", min="0" max="65535">
                        <select id="port_protocol">
                            <option value="1">TCP Socket</option>
                            <option value="2">UDP Socket</option>
                            <option value="3">HTTPS Request</option>
                            <option value="4">HTTPS Request (SS)</option>
                            <option value="5">HTTP Request</option>
                        </select>
                        <button id="submitHostPort"><?= $lng['L_SEND'] ?></button>
                        <?php
                        if (!empty($tdata['host_details']['remote_ports'])) :
                            ?>
                        <select class="current_remote_ports">
                            <?php
                            foreach ($tdata['host_details']['remote_ports'] as $port) :
                                $port_proto = match ((int)$port['protocol']) {
                                    1 => 'TCP Socket',
                                    2 => 'UDP Socket',
                                    3 => 'HTTPS Request',
                                    4 => 'HTTPS Request(SS)',
                                    5 => 'HTTP Request',
                                };
                                $port_name = "{$port['pnumber']}($port_proto)";
                                ?>
                            <option value="<?= $port['id'] ?>"><?= $port_name ?></option>
                                <?php
                            endforeach;
                            ?>
                        </select>
                        <button class="deleteRemoteHostPort"><?= $lng['L_DELETE'] ?></button>
                            <?php
                        endif;
                        ?>
                    </div>
                    <div class="">
                        <label for="host_timeout"><?= $lng['L_TIMEOUT'] ?> (0.0): </label><br />
                        <input
                            size="4" max-size="4" step="0.1" min="0.1" max="5"
                            type="number" id="host_timeout" name="host_timeout"
                            value="<?=
                                !empty($h_misc['timeout']) ?
                                $h_misc['timeout'] : $ncfg->get('port_timeout_local');
                                ?>"
                        />
                        <button id="submitHostTimeout"><?= $lng['L_SEND'] ?></button>
                    </div>

                </div>
                <!-- /left config column -->
                <!-- right config column -->
                <div class="right-config-column">
                    <div class="">
                        <?php if (!empty($tdata['host_details']['agent_installed'])) : ?>
                            <div>Agent</div>
                            <?php
                            if (!empty($tdata['host_details']['agent_ports'])) :
                                ?>
                            <select class="current_agent_ports">
                                <?php
                                foreach ($tdata['host_details']['agent_ports'] as $port) :
                                    !isset($first_service) ? $first_service = $port['custom_service'] : null;
                                    $port_protocol = (int) $port['protocol'] === 1 ? 'TCP' : 'UDP';
                                    $port['online'] ? $port_online = '* ' : $port_online = '';
                                    $port_name = "$port_online{$port['pnumber']}/$port_protocol"
                                        . " {$port['interface']} {$port['service']}"
                                    ?>
                                    <option
                                        value="<?= $port['id'] ?>"
                                        data-cservice="<?= $port['custom_service'] ?>"
                                    >
                                        <?= $port_name ?>
                                    </option>
                                    <?php
                                endforeach;
                                ?>
                            </select>
                            <button class="deleteAgentHostPort"><?= $lng['L_DELETE'] ?></button>
                            <br/>
                            <input
                                id="custom_service_name"
                                type="text"
                                size="12"
                                value="<?= !empty($first_service) ? $first_service : null; ?>"
                            />
                            <button class="submitCustomServiceName"><?= $lng['L_SEND'] ?></button>
                                <?php
                            endif;
                            ?>
                        <?php endif; ?>
                        <div>
                        <label for="reports_stats">Disable Stats</label>
                        <input type="checkbox" id="disable_stats" disabled/>
                        </div>
                        <div>
                        <label for="reports_ports">Disable Report Ports</label>
                        <input type="checkbox" id="disable_report_ports" disabled/>
                        </div>
                        <div>
                        <label for="reports_services">Report Services</label>
                        <input type="checkbox" id="report_services" disabled/>
                        <label for="monitor_services">Monitor Services</label>
                        <select id="monitor_services" disabled></select>
                        </div>
                        <div>Ansible</div>
                        <div>
                        <label for="ansible_recovery">Recovery Services</label>
                        <input type="checkbox" id="ansible_recovery" disabled/>
                        <label for="ansible_recovery_service">Recovery Service</label>
                        <select id="ansible_recovery_service" disabled></select>
                        <label for="ansible_recovery_service_playbook">Recovery Playbook</label>
                        <select id="ansible_recovery_service_playbook" disabled></select>
                        <!-- Mutliple services check? -->
                        <div>Ansible Reports</div>
                        <label for="ansible_boot_report">Boot Report</label>
                        <input type="checkbox" id="ansible_boot_report" disabled />
                        <label for="ansible_boot_report_playbook">Boot Report Playbook</label>
                        <select id="ansible_boot_report_playbook" disabled></select>
                        </div>
                    </div>
                </div>
                <!-- /right config column -->

            </div>
        </div>
        <!-- TAB15 --><!-- Tasks -->
        <div id="tab15" class="host-details-tab-content">
            <div id="tasks_status_msg" class="center">&nbsp</div>
            <div class="create_task">
                <fieldset>
                    <legend>Create Task</legend>
                    <table>
                        <!-- Fila de labels -->
                        <tr>
                            <td><label for="task_name"><?= $lng['L_NAME'] ?></label></td>
                            <td><label for="task_trigger"><?= $lng['L_TASK_TRIGGER'] ?></label></td>
                            <td><label for="conditional_field"><?= $lng['L_CONDITIONAL'] ?></label></td>
                            <td><label for="playbook">Playbook</label></td>
                            <td><label for="ansible_groups"><?= $lng['L_GROUPS']?></label></td>
                            <td><label for="disable_task"><?= $lng['L_DISABLE']?></label></td>
                            <td><label for="next_task"><?= $lng['L_NEXT_TASK']?></label></td>
                            <td></td>
                        </tr>
                        <!-- Fila de inputs -->
                        <tr data-id="0">
                            <td>
                                <input type="hidden" name="hid" value="<?= $tdata['host_details']['id']?>"/>
                                <input type="text" size="12" max-size="12" id="task_name" name="task_name" required>
                            </td>
                            <td>
                                <select id="task_trigger" name="task_trigger" required>
                                    <option value="" disabled selected>Select Trigger</option>
                                    <?php
                                    foreach ($ncfg->get('task_trigger') as $task) :
                                        print("<option value={$task['id']}>{$lng[$task['name']]}</option>");
                                    endforeach;
                                    ?>
                                </select>
                            </td>
                            <td id="conditional_field"></td>
                            <td>
                                <select id="playbooks" name="playbooks">
                                </select>
                            </td>
                            <td> <!-- Groups -->
                                <select id="ansible_groups" disabled name="ansible_groups">
                                    <option value="0" selected disabled><?= $lng['L_THIS_SYSTEM'] ?></option>
                                </select>
                            </td>
                            <td><input type="checkbox" id="disable_task" name="disable_task"></td>
                            <td>
                                <select id="next_task" name="next_task" disabled>
                                    <option value="0" selected>No Next Task</option>
                                </select>
                            </td>
                            <td>
                                <button type="submit" data-action="create_host_task">Create</button>
                            </td>
                        </tr>
                    </table>
                    <input
                        type="hidden"
                        id="event_data"
                        data-input-events="<?= htmlspecialchars(json_encode(EventType::getConstants()))?>"
                    />
                </fieldset>
            </div>
            <div id="tasks-list" class="task-list"></div>
        </div>
        <!-- /TAB15 --><!-- /Tasks -->
        <!-- TAB20 --><!-- Ansible -->
        <div id="tab20" class="host-details-tab-content">
            <div id="ansible_container" class="ansible_container">
                <div class="left-details-column">
                    <div>
                        <div class="playbooks_counter"><?= $lng['L_AVAILABLE_PB']?>: <span id="playbook_count">0</span></div>
                        <div class="playbook_select_container">
                            <select id="playbook_select">
                                <option value=""><?= $lng['L_SEL_PLAYBOOK']?></option>
                            </select>
                            <label for="as_html">HTML</label>
                            <input id="as_html" type="checkbox" checked>
                            <button id="pbqueue_btn"><?= $lng['L_ENQUEUE'] ?></button>
                            <button id="pbexec_btn">Exec</button>
                        </div>
                        <div id="playbook_desc"></div>
                        <div id="vars_container"></div>
                        <div class="ansible_vars">
                            <input type="hidden" data-hid="<?= $tdata['host_details']['id'] ?>"></input>
                            <input type="text" data-name="ans_var_name" size="8" placeholder="Var name"></input>
                            <select id="ans_var_type">
                                <option value="stricname_value">Strict Name -></option>
                                <option value="encrypt_value">Encrypt -></option>
                            </select>
                            <input type="text" data-name="ans_var_value" size="10" placeholder="Var value"></input>
                            <button id="addvar_btn"><?= $lng['L_ADD_VAR'] ?></button>
                            <br/>
                            <select id="ans_var_list">
                            </select>
                            <button id="delete_var_btn"><?= $lng['L_DELETE'] ?></button>
                        </div>
                    </div>
                </div>
                <!-- /left config column -->
                <!-- right config column -->
                <div class="right-details-column">
                        <div id="tags_filter">
                        </div>
                </div>
                <div id="reports-table" class="reports-table"></div>
                <div class="bottom-details-row">
                    <div id="playbook_content" style="border:0px solid blue"><p></p></div>
                </div>
            </div>
        </div>
        <!-- /TAB20 -->
        <!-- TAB16 --><!-- Agent  -->
        <?php
        if ($tdata['host_details']['agent_installed']) :
        ?>
        <div id="tab16" class="host-details-tab-content">
            <div id="agent_container" class="agent_container">
                <div>Log Level</div>
                <div>
                    <select id="log_level_agent" name="log_level_agent">
                        <option value="7">LOG_DEBUG</option>
                        <option value="6">LOG_INFO</option>
                        <option value="5">LOG_NOTICE</option>
                        <option value="4">LOG_WARNING</option>
                        <option value="3">LOG_ERROR</option>
                        <option value="2">LOG_CRITICAL</option>
                        <option value="1">LOG_ALERT</option>
                        <option value="0">LOG_EMERGENCY</option>
                    </select>
                </div>
                <div><?= $lng['L_MEMORY'] . ' ' . $lng['L_THRESHOLD'] ?></div>
                <label for="mem_alert_threshold"><?= $lng['L_ALERTS'] ?></label>
                <input
                    id="mem_alert_threshold"
                    name="mem_alert_threshold"
                    size="2"
                    max-size="2"
                    type="number"
                    value="<?= $h_misc['mem_alert_threshold']?>"
                />
                <label for="mem_warn_threshold"><?= $lng['L_WARNS'] ?></label>
                <input
                    id="mem_warn_threshold"
                    name="mem_warn_threshold"
                    size="2"
                    max-size="2"
                    type="number"
                    value="<?= $h_misc['mem_warn_threshold']?>"
                />
                <div><?= $lng['L_DISKS'] . ' ' . $lng['L_THRESHOLD'] ?></div>
                <label for="disks_alert_threshold"><?= $lng['L_ALERTS'] ?></label>
                <input
                    id="disks_alert_threshold"
                    name="disks_alert_threshold"
                    size="2" max-size="2"
                    type="number"
                    value="<?= $h_misc['disks_alert_threshold']?>"
                />
                <label for="disks_warn_threshold"><?= $lng['L_WARNS'] ?></label>
                <input
                    id="disks_warn_threshold"
                    name="disks_warn_threshold"
                    min="0"
                    max="100"
                    size="2"
                    max-size="2"
                    type="number"
                    value="<?= $h_misc['disks_warn_threshold']?>"
                />
            </div>
        </div>
        <?php endif; ?>
        <!-- /TAB16 -->
    </div> <!-- host-details-container -->
    <!-- host-details -->
    <script>
        $(document).ready(function () {
            // Verificar si el script ya ha sido cargado
            if ($('script[src="scripts/host-details.js"]').length === 0) {
                // Si no está cargado, lo añadimos dinámicamente
                $('<script>', {
                    src: 'scripts/host-details.js',
                    type: 'text/javascript'
                }).appendTo('head');
            }
        });
    </script>
</div>
