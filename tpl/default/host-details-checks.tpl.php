<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
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
