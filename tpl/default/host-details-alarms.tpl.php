<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
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
