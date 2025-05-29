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
            <div class="alarms-checkbox-row">
                <label for="disableAlarms"><?= $lng['L_DISABLE_ALL_ALARMS'] ?>:
                    <input type="checkbox"
                        id="disableAlarms"
                        onchange="submitCommand('setHostAlarms',{id: <?= $tdata['host_details']['id']?>, value: this.checked})"
                        <?= !empty($h_misc['disable_alarms']) ? 'checked' : null ?>>
                </label>
            </div>
            <div><?= $lng['L_DISABLE_PER_TYPE']?></div>
            <div class="alarms-checkbox-row">
                <label>
                    <?= $lng['L_ALARM_PING'] ?>
                    <input type="checkbox"
                        id="alarm_ping_disable"
                        data-command="alarm_ping_disable"
                        <?= !empty($h_misc['alarm_ping_disable']) ? 'checked' : null ?>>
                </label>
                <label>
                    <?= $lng['L_ALARM_PING_PORT'] ?>
                    <input type="checkbox"
                        id="alarm_port_disable"
                        data-command="alarm_port_disable"
                        <?= !empty($h_misc['alarm_port_disable']) ? 'checked' : null ?>>
                </label>
                <label>
                    <?= $lng['L_ALARM_MACCHANGE'] ?>
                    <input type="checkbox"
                        id="alarm_macchange_disable"
                        data-command="alarm_macchange_disable"
                        <?= !empty($h_misc['alarm_macchange_disable']) ? 'checked' : null ?>>
                </label>
                <label>
                    <?= $lng['L_ALARM_NEW_PORT'] ?>
                    <input disabled type="checkbox"
                        id="alarm_newport_disable"
                        data-command="alarm_newport_disable"
                        <?= !empty($h_misc['alarm_newport_disable']) ? 'checked' : null ?>>
                </label>
                <label>
                    <?= $lng['L_ALARM_HOSTNAME'] ?>
                    <input disabled type="checkbox"
                        id="alarm_hostname_disable"
                        data-command="alarm_hostname_disable"
                        <?= !empty($h_misc['alarm_hostname_disable']) ? 'checked' : null ?>>
                </label>
            </div>
            <div class="alarms-checkbox-row" style="margin-top:8px;">
                <label for="enableEmailAlarms"><?= $lng['L_EMAIL_ALARMS'] ?>:
                    <input
                        type="checkbox" id="toggleMailAlarms"
                        data-command="toggleMailAlarms"
                        <?= !empty($h_misc['email_alarms']) ? 'checked' : null ?>>
                </label>
            </div>
            <div><?= $lng['L_ENABLE_PER_TYPE']?></div>
            <div class="alarms-checkbox-row">
                <label>
                    <?= $lng['L_ALARM_PING'] ?>
                    <input type="checkbox"
                        id="alarm_ping_email"
                        data-command="alarm_ping_email"
                        <?= !empty($h_misc['alarm_ping_email']) ? 'checked' : null ?>>
                </label>
                <label>
                    <?= $lng['L_ALARM_PING_PORT'] ?>
                    <input type="checkbox"
                        id="alarm_port_email"
                        data-command="alarm_port_email"
                        <?= !empty($h_misc['alarm_port_email']) ? 'checked' : null ?>>
                </label>
                <label>
                    <?= $lng['L_ALARM_MACCHANGE'] ?>
                    <input type="checkbox"
                        id="alarm_macchange_email"
                        data-command="alarm_macchange_email"
                        <?= !empty($h_misc['alarm_macchange_email']) ? 'checked' : null ?>>
                </label>
                <label>
                    <?= $lng['L_ALARM_NEW_PORT'] ?>
                    <input disabled type="checkbox"
                        id="alarm_newport_email"
                        data-command="alarm_newport_email"
                        <?= !empty($h_misc['alarm_newport_email']) ? 'checked' : null ?>>
                </label>
            </div>
            <div style="margin-top:8px;">
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
        </div>
        <div>
            <button id="clear_host_alarms"
                onclick="submitCommand('clearHostAlarms',{id: <?= $tdata['host_details']['id'] ?>})">
                <?= $lng['L_CLEAR_ALARMS_BITS'] ?>
            </button>
        </div>
    </div>
</div>
