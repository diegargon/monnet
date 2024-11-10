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
                            onclick="changeTab('tab1')"><?= $lng['L_OVERVIEW'] ?>
                    </button>
                    <?php if (!empty($tdata['host_details']['access_method'])) : ?>
                        <button id="tab2_btn" class="host-details-tabs-head" data-tab="2"
                                onclick="changeTab('tab2')"><?= $lng['L_STATUS'] ?>
                        </button>
                    <?php endif; ?>
                    <button id="tab3_btn" class="host-details-tabs-head" data-tab="3"
                            onclick="changeTab('tab3')"><?= $lng['L_NOTES'] ?>
                    </button>
                    <?php if (!empty($tdata['host_details']['host_logs'])) : ?>
                        <button id="tab9_btn" class="host-details-tabs-head" data-tab="9"
                                onclick="changeTab('tab9')"><?= $lng['L_LOG'] ?>
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($tdata['host_details']['ping_graph'])) : ?>
                        <button id="tab10_btn" class="host-details-tabs-head" data-tab="10"
                                onclick="changeTab('tab10')"><?= $lng['L_METRICS'] ?>
                        </button>
                    <?php endif; ?>
                    <button id="tab11_btn" class="host-details-tabs-head" data-tab="11"
                            onclick="changeTab('tab11')">
                        <?= $lng['L_ALARMS'] ?>
                    </button>
                    <button id="tab12_btn" class="host-details-tabs-head" data-tab="12"
                            onclick="changeTab('tab12')">
                        <?= $lng['L_CONFIG'] ?>
                    </button>
                    <!--
                    <button id="tabx_btn" class="host-details-tabs-head" onclick="changeTab('tab2')">
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
                <?php if (
                        !empty($tdata['host_details']['access_method']) &&
                        !empty($tdata['host_details']['online'])
                ) { ?>
                    <input onClick="submitCommand('power_off', {id:<?= $tdata['host_details']['id'] ?>})" type="image"
                           class="action-icon power-on" src="tpl/<?= $cfg['theme'] ?>/img/power-on.png"
                           alt="<?= $lng['L_PWR_OFF'] ?>" title="<?= $lng['L_PWR_OFF'] ?>"/>
                <?php } ?>
                <?php if (!empty($tdata['host_details']['access_method'])) : ?>
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
            <?php if (
                    !empty($tdata['host_details']['mac_vendor']) &&
                    $tdata['host_details']['mac_vendor'] != '-'
            ) { ?>
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
                <?php if (
                    !empty($tdata['host_details']['uptime']) &&
                    is_array($tdata['host_details']['uptime'])
                ) { ?>
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
                cols="100"><?= $tdata['host_details']['notes'] ?></textarea>
        </div>
        <!-- /TAB3 -->
        <!-- TAB9 --><!-- Host Logs -->
        <?php
        if (!empty($tdata['host_details']['host_logs'])) {
            ?>
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
                    <?= $tdata['host_details']['host_logs'] ?>
                </div>
        <?php } ?>
        <!-- /TAB9 -->
        <!-- TAB10 --><!-- Graphs / PING -->
        <?php
        if (!empty($tdata['host_details']['ping_graph'])) {
            ?>
            <div id="tab10" class="host-details-tab-content">
                <div class="ping_graph_container">
                    <?= $tdata['host_details']['ping_graph'] ?>
                </div>
            </div>
        <?php } ?>
        <!-- /TAB10 -->
        <!-- TAB11 -->
        <div id="tab11" class="host-details-tab-content">
            <div class="alarms_container">
                <div class="">
                    <label for="disableAlarms"><?= $lng['L_DISABLE_ALARMS'] ?>:</label>
                    <input type="hidden" id="disableAlarms" value="0">
                    <input type="checkbox" id="disableAlarms"
                           <?= isset($tdata['host_details']['alarms_off']) ? 'checked' : null ?>>
                    <label for="enableEmailAlarms"><?= $lng['L_ENABLE_EMAIL_ALARMS'] ?>:</label>
                    <input type="hidden" id="enableMailAlarms" value="0">
                    <input type="checkbox" id="enableMailAlarms"
                           <?= isset($tdata['host_details']['alarms_email_on']) ? 'checked' : null ?>>
                    <div>Tipo Alarmas</div>
                    <label for="">Ping Host Fail:</label>
                    <input type="hidden" id="alarm_ping_onoff" value="0">
                    <input type="checkbox" id="alarm_ping_onoff">
                    <label for="">Ping Port Fail:</label>
                    <input type="hidden" id="alarm_port_onoff" value="0">
                    <input type="checkbox" id="alarm_port_onoff">
                    <label for="">Cambio MAC:</label>
                    <input type="hidden" id="alarm_mac_change" value="0">
                    <input type="checkbox" id="alarm_mac_change">
                    <label for="">Puerto Nuevo:</label>
                    <input type="hidden" id="alarm_new_port" value="0">
                    <input type="checkbox" id="alarm_new_port">
                    <br/>
                    <label for="">Emails (Comma Separated)</label><br/>
                    <input type="text" size="50" id="alarm_emails">
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
                        <label for="chkHighlight"><?= $lng['L_HIGHLIGHT_HOSTS'] ?>:</label>
                        <input type="checkbox"
                               id="chkHighlight" <?= $tdata['host_details']['highlight'] ? 'checked' : null ?>>
                        <input type="number" id="host_id" name="host_id"
                               style="display:none;" readonly value="<?= $tdata['host_details']['id'] ?>"/>
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
                        <label for="host_timeout"><?= $lng['L_TIMEOUT'] ?>(0.0): </label><br />
                        <input size="12" max-size="12" type="number" id="host_timeout" name="host_timeout"
                               value="<?=
                                !empty($tdata['host_details']['timeout']) ?
                                $tdata['host_details']['timeout'] : null
                                ?>"/>
                        <button id="submitHostTimeout"><?= $lng['L_SEND'] ?></button>
                    </div>
                    <div class="">
                        <label for="manufacture"><?= $lng['L_MANUFACTURE'] ?>: </label><br/>
                        <select id="manufacture">
                            <?php foreach ($cfg['manufacture'] as $manufacture) : ?>
                                <?php
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
                            <?php foreach ($cfg['os'] as $os) : ?>
                                <?php
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
                            <?php foreach ($cfg['system_type'] as $system_type) : ?>
                                <?php
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

        <!-- TODO DISABLED -->
        <!--
        <?php
        if (!empty($tdata['host_details']['access_method'])) {
            ?>
        </div>
        <?php } ?>
        -->
        <!-- DEPLOYS -->
        <!--
        <?php
        if (!empty($tdata['host_details']['deploys']) && valid_array($tdata['host_details']['deploys'])) {
            ?>
        <option value="0"></option>
            <?php
            foreach ($tdata['host_details']['deploys'] as $k_deploy => $deploy) {
                ?>
        <option value="<?= $k_deploy ?>"><?= $deploy['name'] ?></option>
                <?php
            }
            ?>
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
        <!-- /TODO DISABLED -->
    </div> <!-- host-details-container -->
    <!-- host-details -->
    <script src="scripts/host-details.js"></script>
</div>
