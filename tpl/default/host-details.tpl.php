<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<int|string, mixed> $cfg
 * @var Config $ncfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
//var_dump($tdata['host_details']);
?>

<div id="host-details" class="host-details draggable" style="display:block">
    <div id="host-details-container" class="host-details-container">
        <!-- HOST COMMON BAR -->
        <!-- FIRST HEADED BAR -->
        <div class="host-details-bar dragbar">
            <div class="host-controls-left">
                <button id="close_host_details" type="submit"  class="button-ctrl">
                    <img src="tpl/<?= $cfg['theme'] ?>/img/close.png"
                         alt="<?= $lng['L_CLOSE'] ?>" title="<?= $lng['L_CLOSE'] ?>" />
                </button>
                <div class="host-details-tabs-head-container">
                    <button id="tab1_btn" class="host-details-tabs-head" data-tab="1"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab1')">
                            <?= $lng['L_OVERVIEW'] ?>
                    </button>
                    <?php if (!empty($tdata['host_details']['access_method'])) : ?>
                        <button id="tab2_btn" class="host-details-tabs-head" data-tab="2"
                                onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab2')">
                                <?= $lng['L_STATUS'] ?>
                        </button>
                    <?php endif; ?>
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
                    <button id="tab13_btn" class="host-details-tabs-head" data-tab="13"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab13')">
                        <?= $lng['L_CHECKS'] ?>
                    </button>
                    <?php if (!empty($ncfg->get('ansible')) && !empty($tdata['host_details']['ansible_enabled'])) : ?>
                    <button id="tab20_btn" class="host-details-tabs-head" data-tab="20"
                            onclick="changeHDTab(<?= $tdata['host_details']['id']?>, 'tab20')">
                            Ansible Raw
                    </button>
                    <?php endif; ?>
                    <!--
                    <button id="tabx_btn" class="host-details-tabs-head" onclick="changeHDTab('tab2')">
                        <?= $lng['L_DEPLOYS'] ?>
                    </button>
                    -->
                </div>
            </div> <!--host-controls-right -->
            <div class="host-controls-right">
                <?php if (!empty($tdata['host_details']['mac']) && empty($tdata['host_details']['online'])) : ?>
                    <input onClick="submitCommand('power_on', {id: <?= $tdata['host_details']['id'] ?>})" type="image"
                           class="action-icon power-off" src="tpl/<?= $cfg['theme'] ?>/img/power-off.png"
                           alt="<?= $lng['L_PWR_ON'] ?>" title="<?= $lng['L_PWR_ON'] ?>"/>
                <?php endif; ?>
                <?php
                if (
                    !empty($tdata['host_details']['ansible_enabled']) &&
                    !empty($tdata['host_details']['online'])
                ) {
                    ?>
                    <input onClick="submitCommand('shutdown', {id:<?= $tdata['host_details']['id'] ?>})" type="image"
                           class="action-icon power-on" src="tpl/<?= $cfg['theme'] ?>/img/power-on.png"
                           alt="<?= $lng['L_PWR_OFF'] ?>" title="<?= $lng['L_PWR_OFF'] ?>"/>
                <?php } ?>
                <?php if (!empty($tdata['host_details']['ansible_enabled'])) : ?>
                <input onClick="submitCommand('reboot', {id:<?= $tdata['host_details']['id'] ?>})" type="image"
                           class="action-icon reboot" src="tpl/<?= $cfg['theme'] ?>/img/reboot.png"
                           alt="<?= $lng['L_REBOOT'] ?>" title="<?= $lng['L_REBOOT'] ?>"/>
                <?php endif; ?>
                <input onClick="confirmSubmit('remove_host',{id:<?= $tdata['host_details']['id']?>})" type="image"
                       class="action-icon remove" src="tpl/<?= $cfg['theme'] ?>/img/remove.png"
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

            <?php if (!empty($tdata['host_details']['system_type_image'])) : ?>
                <img class="fab" src="<?= $tdata['host_details']['system_type_image'] ?>"
                     alt="<?= $tdata['host_details']['system_type_name'] ?>"
                     title="<?= $tdata['host_details']['system_type_name'] ?>"/>
            <?php endif; ?>
            <?php if (!empty($ncfg->get('ansible')) && !empty($tdata['host_details']['ansible_enabled'])) : ?>
                <img class="fab" src="tpl/<?= $tdata['theme']?>/img/ansible.png"
                     alt="ansible" title="ansible"/>
            <?php endif; ?>
            <?php if (!empty($tdata['host_details']['vm_machine'])) : ?>
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
                !empty($tdata['host_details']['mac_vendor']) &&
                $tdata['host_details']['mac_vendor'] != '-'
            ) {
                ?>
                <div class="host-item"><?= $tdata['host_details']['mac_vendor'] ?> </div>
            <?php } ?>
        </div>
        <!-- THIRD HEADED BAR -->
        <?php
        if ($tdata['host_details']['check_method'] == 2 && !empty($tdata['host_details']['ports'])) {
            ?>
            <div class="host_port_container">
                <?php
                foreach ($tdata['host_details']['ports'] as $port) {
                    ?>
                    <div class="port_container">
                        <?php if ($port['online']) : ?>
                            <img class="port-online" src="tpl/<?= $tdata['theme'] ?>/img/green2.png" alt=""/>
                        <?php else : ?>
                            <img class="port-offline" src="tpl/<?= $tdata['theme'] ?>/img/red2.png" alt=""/>
                        <?php endif; ?>
                        <div class="host_port_name"><?= $port['name'] ?></div>
                        <div class="host_port">(<?= $port['n'] ?>)</div>
                    </div> <!-- port container -->
                <?php } ?>
            </div> <!-- host port container -->
        <?php } ?>
        <!-- /HOST COMMON BAR -->
        <!-- TAB1  RESUME -->
        <div id="tab1" class="host-details-tab-content">
            <div class="">
                <div class="">
                    <label class="resume_label"><?= $lng['L_NAME'] ?>:</label>
                    <span class="display_name"><?= $tdata['host_details']['display_name'] ?></span>
                </div>
                <div class"">
                    <div><?= $lng['L_NETWORK'] ?>: <?= $tdata['host_details']['net_cidr'] ?></div>
                    <div><?= $lng['L_NETWORK_NAME'] ?>: <?= $tdata['host_details']['network_name'] ?></div>
                    <div><?= $lng['L_VLAN'] ?>: <?= $tdata['host_details']['network_vlan'] ?></div>
                </div>
                <?php if (!empty($tdata['host_details']['owner'])) : ?>
                    <div class="">
                        <label class="resume_label"><?= $lng['L_OWNER'] ?>:</label>
                        <span class="resume_field"><?= $tdata['host_details']['owner'] ?></span>
                    </div>
                <?php endif; ?>
                <div class="">
                    <label class="resume_label"><?= $lng['L_ADDED'] ?>:</label>
                    <span class="resume_field">
                        <?= $tdata['host_details']['formated_creation_date'] ?>
                    </span>
                </div>
                <?php
                if (
                    !empty($tdata['host_details']['uptime']) &&
                    is_array($tdata['host_details']['uptime'])
                ) {
                    ?>
                    <div class="" >
                        <label class="resume_label"><?= $lng['L_UPTIME'] ?>:</label>
                        <span class="resume_field">
                            <?= $tdata['host_details']['uptime']['datetime'] ?>
                        </span>
                    </div>
                <?php } ?>
                <?php if (!empty($tdata['host_details']['latency_ms'])) : ?>
                    <div class="" >
                        <label class="resume_label"><?= $lng['L_LATENCY'] ?>:</label>
                        <span class="resume_field"><?= $tdata['host_details']['latency_ms'] ?></span>
                    </div>
                <?php endif; ?>

                <?php
                if (
                        empty($tdata['host_details']['online']) &&
                        !empty($tdata['host_details']['f_last_seen'])
                ) {
                    ?>
                    <div>
                        <label class="resume_label"><?= $lng['L_LAST_SEEN'] ?>:</label>
                        <span class="resume_field"><?= $tdata['host_details']['f_last_seen'] ?></span>
                    </div>
                <?php } ?>

                <?php if (!empty($tdata['host_details']['f_last_check'])) : ?>
                    <div>
                        <label class="resume_label"><?= $lng['L_LAST_CHECK'] ?>:</label>
                        <span class="resume_field"><?= $tdata['host_details']['f_last_check'] ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($tdata['host_details']['access_link'])) : ?>
                    <div>
                        <a href="<?= $tdata['host_details']['access_link'] ?>"
                           target="_blank"><?= $tdata['host_details']['access_link'] ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- /TAB1 -->
        <!-- TAB2 DETAILS -->
        <?php if (!empty($tdata['host_details']['access_method'])) { ?>
            <div id="tab2" class="host-details-tab-content">
                <div id="progress_bars">
                    <?php if (!empty($tdata['host_details']['f_loadavg'])) : ?>
                        <label for="load_avg"><?= $lng['L_LOAD'] ?>:</label>
                        <progress id="load_avg" value="<?= $tdata['host_details']['f_loadavg'] ?>"
                                  max="<?= $tdata['host_details']['f_maxload'] ?>"
                                  data-label="<?= $tdata['host_details']['f_loadavg'] ?>">
                        </progress>
                    <?php endif; ?>
                    <?php if (!empty($tdata['host_details']['mem'])) :
                        $mem = $tdata['host_details']['mem'];
                        ?>
                        <label for="mem"><?= $lng['L_MEM'] ?>:</label>
                        <progress id="mem" value="<?= $mem['mem_used'] ?>" max="<?= $mem['mem_available'] ?>">
                        </progress>
                    <?php endif; ?>
                    <?php
                    if (
                            !empty($tdata['host_details']['disks']) &&
                            count($tdata['host_details']['disks']) > 0
                    ) {
                        ?>
                        <?php foreach ($tdata['host_details']['disks'] as $disk) : ?>
                            <label class="disk"><?= $disk['mounted'] ?>:</label>
                            <progress class="disk" value="<?= $disk['used_percent'] ?>" max="100"></progress>
                        <?php endforeach; ?>
                    <?php } ?>
                </div> <!-- progress container -->
            </div>
        <?php } ?>
        <!-- /TAB2 -->
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
                <div><button id="logs_reload_btn">Reload</button></div>
                <div>
                    <select id="log_level" name="log_level">
                        <option value="-1">LOG_ALL</option>
                        <option value="0">LOG_EMERG</option>
                        <option value="1">LOG_ALERT</option>
                        <option value="2">LOG_CRIT</option>
                        <option value="3">LOG_ERR</option>
                        <option value="4">LOG_WARNING</option>
                        <option value="5">LOG_NOTICE</option>
                        <option value="6">LOG_INFO</option>
                        <option value="7">LOG_DEBUG</option>
                    </select>
                </div>
            </div>
            <label for="log_size">Nº:</label>
            <input type="number" id="log_size" name="log_size" step="25" value="25">
            <?php if (!empty($ncfg->get('ansible')) && !empty($tdata['host_details']['ansible_enabled'])) : ?>
            <div class="inline"><button id="syslog_btn">Syslog</button></div>
            <div class="inline"><button id="journald_btn">Journald</button></div>
            <?php endif; ?>
            <?= $tdata['host_details']['host_logs'] ?>
        </div>
        <!-- /TAB9 -->
        <!-- TAB10 --><!-- Graphs / PING -->
        <div id="tab10" class="host-details-tab-content">
            <div id="ping_graph_container" class="ping_graph_container">
                <?= $tdata['host_details']['ping_graph'] ?? '' ?>
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
                           <?= !empty($tdata['host_details']['disable_alarms']) ? 'checked' : null ?>>
                    <div><?= $lng['L_DISABLE_PER_TYPE']?></div>
                    <label for=""><?= $lng['L_ALARM_PING']?>:</label>
                    <input type="hidden" id="alarm_ping_disable" value="0">
                    <input type="checkbox"
                           id="alarm_ping_disable"
                           data-command="alarm_ping_disable"
                           <?= !empty($tdata['host_details']['alarm_ping_disable']) ? 'checked' : null ?>
                           >
                    <label for=""><?= $lng['L_ALARM_PING_PORT']?>:</label>
                    <input type="hidden" id="alarm_port_disable" value="0">
                    <input type="checkbox"
                           id="alarm_port_disable"
                           data-command="alarm_port_disable"
                           <?= !empty($tdata['host_details']['alarm_port_disable']) ? 'checked' : null ?>
                           >
                    <label for=""><?= $lng['L_ALARM_MACCHANGE']?>:</label>
                    <input type="hidden" id="alarm_macchange_disable" value="0">
                    <input type="checkbox"
                           id="alarm_macchange_disable"
                           data-command="alarm_macchange_disable""
                           <?= !empty($tdata['host_details']['alarm_macchange_disable']) ? 'checked' : null ?>
                           >
                    <label for=""><?= $lng['L_ALARM_NEW_PORT']?>:</label>
                    <input disable type="hidden" id="alarm_newport_disable" value="0">
                    <input disable type="checkbox"
                           id="alarm_newport_disable"
                           data-command="alarm_newport_disable"
                           <?= !empty($tdata['host_details']['alarm_newport_disable']) ? 'checked' : null ?>
                           >
                    <br/>
                    <label for="enableEmailAlarms"><?= $lng['L_EMAIL_ALARMS'] ?>:</label>
                    <input
                        type="checkbox" id="toggleMailAlarms"
                        data-command="toggleMailAlarms"
                        <?= !empty($tdata['host_details']['email_alarms']) ? 'checked' : null ?>
                    >
                    <br/>
                    <div><?= $lng['L_ENABLE_PER_TYPE']?></div>
                    <label for=""><?= $lng['L_ALARM_PING']?>:</label>
                    <input type="hidden" id="alarm_ping_email" value="0">
                    <input type="checkbox"
                           id="alarm_ping_email"
                           data-command="alarm_ping_email"
                           <?= !empty($tdata['host_details']['alarm_ping_email']) ? 'checked' : null ?>
                           >
                    <label for=""><?= $lng['L_ALARM_PING_PORT']?>:</label>
                    <input type="hidden" id="alarm_port_email" value="0">
                    <input type="checkbox"
                           id="alarm_port_email"
                           data-command="alarm_port_email"
                           <?= !empty($tdata['host_details']['alarm_port_email']) ? 'checked' : null ?>
                           >
                    <label for=""><?= $lng['L_ALARM_MACCHANGE']?>:</label>
                    <input type="hidden" id="alarm_macchange_email" value="0">
                    <input type="checkbox"
                           id="alarm_macchange_email"
                           data-command="alarm_macchange_email"
                           <?= !empty($tdata['host_details']['alarm_macchange_email']) ? 'checked' : null ?>
                           >
                    <label for=""><?= $lng['L_ALARM_NEW_PORT']?>:</label>
                    <input disable type="hidden" id="alarm_newport_email" value="0">
                    <input disable type="checkbox"
                           id="alarm_newport_email"
                           data-command="alarm_newport_email"
                           <?= !empty($tdata['host_details']['alarm_newport_email']) ? 'checked' : null ?>
                           >
                    <br/>
                    <label for="alarm_emails">Emails (Comma Separated)</label><br/>
                    <input type="text"
                           size="50"
                           id="alarm_emails"
                           placeholder="Enter emails separated by commas"
                           value="<?php
                            if (!empty($tdata['host_details']['email_list'])) {
                                echo $tdata['host_details']['email_list'];
                            }
                            ?>"
                           >
                    <div id="email_feedback" style="color: red; font-size: 0.9em;"></div>
                </div>
                <div>
                    <div><?= $lng['L_ALARMS_LAST'] ?> :</div>
                    <div><?= $tdata['host_details']['alert_msg'] ?? '' ?></div>
                    <div><?= $tdata['host_details']['warn_msg'] ?? '' ?></div>
                    <button id="clear_alarms"
                             onclick="submitCommand('clearHostAlarms',{id: <?= $tdata['host_details']['id'] ?>})">
                            <?= $lng['L_CLEAR_ALARMS'] ?>
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
                    <div class="">
                        <label for="vm_machine"><?= $lng['L_VIRTUAL_MACHINE'] ?>: </label>
                        <input type="hidden" id="vm_machine" value="0"/>
                        <input type="checkbox" id="vm_machine"
                               <?= !empty($tdata['host_details']['vm_machine']) ? 'checked' : null ?>><br />
                    </div>
                    <div class="">
                        <label for="hypervisor_machine"><?= $lng['L_HYPERVISOR'] ?>: </label>
                        <input type="hidden" id="hypervisor_machine" value="0"/>
                        <input type="checkbox" id="hypervisor_machine"
                               <?= !empty($tdata['host_details']['hypervisor_machine']) ? 'checked' : null ?>><br />
                    </div>
                    <?php if ($ncfg->get('ansible')) : ?>
                    <div class="">
                        <label for=""><?= $lng['L_ANSIBLE_SUPPORT'] ?></label>
                        <input type="hidden" id="ansible_enabled" value="0">
                        <input
                            type="checkbox"
                            id="ansible_enabled"
                            <?= !empty($tdata['host_details']['ansible_enabled']) ? ' checked' : '' ?>>
                    </div>
                    <?php endif; ?>
                    <div class="">
                        <label for="host-title"><?= $lng['L_DISPLAY_NAME'] ?></label><br />
                        <input type="text" id="host-title" size="32" name="host-title"
                               value="<?= $tdata['host_details']['title'] ?>"/>
                        <button id="submitTitle"><?= $lng['L_SEND'] ?></button>
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
                        <button id="submitCat"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="host_owner"><?= $lng['L_OWNER'] ?>: </label><br />
                        <input
                            type="text" id="host_owner" name="host_owner"
                            value="<?=
                                !empty($tdata['host_details']['owner'])
                                ? $tdata['host_details']['owner']
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
                                !empty($tdata['host_details']['access_link'])
                                    ? $tdata['host_details']['access_link']
                                    : null
                            ?>"
                        />
                        <select id="access_link_type" name="access_link_type">
                            <?php foreach ($cfg['access_link_types'] as $key => $access_type) : ?>
                                <option value="<?= $key ?>" selected="1"><?= $access_type ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button id="submitAccessLink"><?= $lng['L_SEND'] ?></button>
                    </div>
                </div>
                <!-- /left config column -->
                <!-- right config column -->
                <div class="right-config-column">
                    <div class="">
                        <label for="machine_type"><?= $lng['L_MACHINE_TYPE'] ?>: </label><br/>
                        <select id="machine_type">
                            <?php foreach ($cfg['machine_type'] as $mtype) :
                                $selected = '';
                                if (
                                    !empty($tdata['host_details']['machine_type']) &&
                                    ($mtype['id'] == $tdata['host_details']['machine_type'])
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
                            <?php foreach ($cfg['manufacture'] as $manufacture) :
                                $selected = '';
                                if (
                                    !empty($tdata['host_details']['manufacture']) &&
                                    ($manufacture['id'] == $tdata['host_details']['manufacture'])
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
                            <?php foreach ($cfg['os'] as $os) :
                                $selected = '';
                                if (
                                    !empty($tdata['host_details']['os']) &&
                                    ($os['id'] == $tdata['host_details']['os'])
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
                        <label for="system_type"><?= $lng['L_SYSTEM_TYPE'] ?>: </label><br/>
                        <select id="system_type">
                            <?php foreach ($cfg['system_type'] as $system_type) :
                                $selected = '';
                                if (
                                    !empty($tdata['host_details']['system_type']) &&
                                    ($system_type['id'] == $tdata['host_details']['system_type'])
                                ) :
                                    $selected = ' selected=1 ';
                                endif;
                                ?>
                                <option value="<?= $system_type['id'] ?>"<?= $selected ?>><?= $system_type['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button id="submitSystemType"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="system_aval"><?= $lng['L_AVAILABILITY'] ?>: </label><br/>
                        <select id="system_aval">
                            <?php foreach ($cfg['sys_availability'] as $sys_aval) :
                                $selected = '';
                                if (
                                    !empty($tdata['host_details']['sys_availability']) &&
                                    ($sys_aval['id'] == $tdata['host_details']['sys_availability'])
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
                        <label for="host_token"><?= $lng['L_TOKEN'] ?>: </label><br/>
                        <input type="text" size="32" id="host_token" name="host_token"
                               value="<?= $tdata['host_details']['token'] ?>" readonly/>
                        <button id="submitHostToken"><?= $lng['L_CREATE'] ?></button>
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
                               <?= !empty($tdata['host_details']['disable_ping']) ? 'checked' : null ?>><br />
                    </div>
                    <div class="">
                        <label for="checkport"><?= $lng['L_PORT_CHECK'] ?>: </label>
                        <input type="checkbox" id="checkports_enabled"
                               <?= $tdata['host_details']['check_method'] == 2 ? 'checked' : null ?>><br />
                        <label for="checkports"><?= $lng['L_PORT_LIST'] ?>
                            (ex: 53/udp/name,443/tcp/name): </label><br />
                        <input type="text" id="checkports" name="checkports"
                               value="<?= $tdata['host_details']['ports_formated'] ?>"/>
                        <button id="submitPorts"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="host_timeout"><?= $lng['L_TIMEOUT'] ?>(0.0): </label><br />
                        <input size="12" max-size="12" type="number" id="host_timeout" name="host_timeout"
                               value="<?=
                                !empty($tdata['host_details']['timeout']) ?
                                $tdata['host_details']['timeout'] : null
                                ?>"/>
                        <button id="submitHostTimeout"><?= $lng['L_SEND'] ?></button>
                    </div>

                </div>
                <!-- /left config column -->
                <!-- right config column -->
                <div class="right-config-column">
                    <div class="">

                    </div>
                </div>
                <!-- /right config column -->

            </div>
        </div>
        <!-- TAB20 --><!-- Ansible -->
        <div id="tab20" class="host-details-tab-content">
            <div id="ansible_container" class="ansible_container"
                 data-playbooks='<?= json_encode($cfg['playbooks']); ?>'>
                <div><button id="playbook_btn">Exec</button></div>
                <select id="playbook_select">
                    <option value="">Select Playbook</option>
                </select>
                <label for="as_html">HTML</label>
                <input id="as_html" type="checkbox" checked>
                <div id="playbook_desc"></div>
                <div id="vars_container"></div>
                <div id="ansible_raw_container" class="ansible_raw_container">
                    <div id="raw_lines_container">
                        <div id="html_lines"></div>
                        <pre id="raw_lines"></pre>
                    </div>
                </div>
            </div>
        </div>
        <!-- /TAB20 -->
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
