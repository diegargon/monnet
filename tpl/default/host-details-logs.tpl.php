<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
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
