<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

$lsel = !empty($h_misc['agent_log_level']) ? $h_misc['agent_log_level'] : 'INFO';
$lsel = strtoupper(trim($lsel));
?>
<div id="tab16" class="host-details-tab-content">
    <div id="agent_container" class="agent_container">
        <div>Log Level</div>
        <div>
            <select id="agent_log_level" name="agent_log_level">
                <option value="DEBUG" <?= $lsel == 'DEBUG' ? ' selected' : ''?>>LOG_DEBUG</option>
                <option value="INFO" <?= $lsel == 'INFO' ? ' selected' : ''?>>LOG_INFO</option>
                <option value="NOTICE" <?= $lsel == 'NOTICE' ? ' selected' : ''?>>LOG_NOTICE</option>
                <option value="WARNING" <?= $lsel == 'WARNING' ? ' selected' : ''?>>LOG_WARNING</option>
                <option value="ERROR" <?= $lsel == 'ERROR' ? ' selected' : ''?>>LOG_ERROR</option>
                <option value="CRITICAL" <?= $lsel == 'CRITICAL' ? ' selected' : ''?>>LOG_CRITICAL</option>
                <option value="ALERT" <?= $lsel == 'ALERT' ? ' selected' : ''?>>LOG_ALERT</option>
                <option value="EMERGENCY" <?= $lsel == 'EMERGENCY' ? ' selected' : ''?>>LOG_EMERGENCY</option>
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
        <br/>
        <button id="submitAgentConfig" type="submit>"><?= $lng['L_SEND'] ?></button>
    </div>
</div>
