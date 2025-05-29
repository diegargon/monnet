<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
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
            <div class="switch-container">
              <input type="checkbox" id="logic-switch" />
              <label for="logic-switch" class="switch-label">
                <span class="switch-inner">
                  <span class="label-and">&</span>
                  <span class="label-or">/</span>
                </span>
                <span class="switch-handle"></span>
              </label>
            </div>
            <div id="tags_filter"></div>
        </div>
        <div id="reports-table" class="reports-table"></div>
        <div class="bottom-details-row">
            <div id="playbook_content" style="border:0px solid blue"><p></p></div>
        </div>
    </div>
</div>
