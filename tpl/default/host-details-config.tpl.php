<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
<div id="tab12" class="host-details-tab-content">
    <div id="config_status_msg"></div>
    <div class="config_container">
        <div class="left-config-column">
            <table class="resume-fields-table">
                <tr>
                    <td class="resume_label" colspan="2">
                        <div class="config-checkbox-row">
                            <label for="chkHighlight">
                                <input type="checkbox"
                                    id="chkHighlight" <?= $tdata['host_details']['highlight'] ? 'checked' : null ?>>
                                <?= $lng['L_HIGHLIGHT_HOSTS'] ?>
                            </label>
                            <?php if ($ncfg->get('ansible')) : ?>
                            <label for="ansible_enabled">
                                <input type="checkbox"
                                    id="ansible_enabled"
                                    <?= !empty($tdata['host_details']['ansible_enabled']) ? ' checked' : '' ?>>
                                <?= $lng['L_ANSIBLE_SUPPORT'] ?>
                            </label>
                            <?php endif; ?>
                            <label for="always_on">
                                <input type="checkbox"
                                    id="always_on"
                                    data-command="setAlwaysOn"
                                    <?= !empty($h_misc['always_on']) ? ' checked' : '' ?>>
                                <?= $lng['L_ALWAYS_ON'] ?>
                            </label>
                            <label for="linkable">
                                <input type="checkbox"
                                    id="linkable"
                                    data-command="setLinkable"
                                    <?= !empty($tdata['host_details']['linkable']) ? ' checked' : '' ?>>
                                <?= $lng['L_LINKABLE'] ?>
                            </label>
                            <label for="disable_host">
                                <input
                                    disabled
                                    type="checkbox"
                                    id="host_on"
                                    data-command="setHostDisable"
                                    <?= !empty($tdata['host_details']['disable']) ? ' checked' : '' ?>>
                                <?= $lng['L_DISABLE'] ?>
                            </label>
                            <input type="number" id="host_id" name="host_id"
                                style="display:none;" readonly value="<?= $tdata['host_details']['id'] ?>"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="host-title"><?= $lng['L_DISPLAY_NAME'] ?></label></td>
                    <td>
                        <input type="text" id="host-title" size="12" max-size="15" name="host-title"
                            value="<?= $tdata['host_details']['title'] ?>"/>
                        <button id="submitTitle"><?= $lng['L_SEND'] ?></button>
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="host-name"><?= $lng['L_HOSTNAME'] ?></label></td>
                    <td>
                        <input type="text" id="host-name" size="30" max-size="40" name="host-title"
                            value="<?= $tdata['host_details']['hostname'] ?>"/>
                        <button id="submitHostname"><?= $lng['L_SEND'] ?></button>
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="host-cat"><?= $lng['L_CATEGORY'] ?></label></td>
                    <td>
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
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="host_owner"><?= $lng['L_OWNER'] ?>: </label></td>
                    <td>
                        <input
                            type="text" id="host_owner" name="host_owner"
                            value="<?=
                                !empty($h_misc['owner'])
                                ? $h_misc['owner']
                                : null
                            ?>"
                        />
                        <button id="submitOwner"><?= $lng['L_SEND'] ?></button>
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="access_link"><?= $lng['L_ACCESS'] ?>: </label></td>
                    <td>
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
                    </td>
                </tr>
            </table>
        </div>
        <!-- /left config column -->
        <!-- right config column -->
        <div class="right-config-column">
            <table class="resume-fields-table">
                <tr>
                    <td class="resume_label"><label for="machine_type"><?= $lng['L_MACHINE_TYPE'] ?>: </label></td>
                    <td>
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
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="manufacture"><?= $lng['L_PROVIDER'] ?>: </label></td>
                    <td>
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
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="os_family"><?= $lng['L_OS_FAMILY'] ?>: </label></td>
                    <td>
                        <select id="os_family">
                            <?php foreach ($ncfg->get('os_family') as $os) :
                                $selected = '';
                                if (
                                    !empty($h_misc['os_family']) &&
                                    ($os['id'] == $h_misc['os_family'])
                                ) :
                                    $selected = ' selected=1 ';
                                endif;
                                ?>
                                <option value="<?= $os['id'] ?>"<?= $selected ?>><?= $os['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button id="submitOSFamily"><?= $lng['L_SEND'] ?></button>
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="os"><?= $lng['L_OS'] ?>: </label></td>
                    <td>
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
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="os_version"><?= $lng['L_VERSION'] ?>: </label></td>
                    <td>
                        <input type="text" size="20" id="os_version" name="os_version"
                            value="<?= $h_misc['os_version'] ?? '' ?>" />
                        <button id="submitOSVersion"><?= $lng['L_SEND'] ?></button>
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="system_rol"><?= $lng['L_ROL'] ?>: </label></td>
                    <td>
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
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="system_aval"><?= $lng['L_AVAILABILITY'] ?>: </label></td>
                    <td>
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
                    </td>
                </tr>
                <tr>
                    <td class="resume_label"><label for="linked_to"><?= $lng['L_LINKED'] ?>: </label></td>
                    <td>
                        <select id="linked_to">
                            <option value="0"><?= $lng['L_NONE'] ?></option>
                            <?php if (!empty($tdata['host_details']['linkable_hosts'])): ?>
                                <?php foreach ($tdata['host_details']['linkable_hosts'] as $host): ?>
                                    <option value="<?= $host['id'] ?>"
                                        <?php if (!empty($tdata['host_details']['linked']) && $tdata['host_details']['linked'] == $host['id']): ?>
                                            selected
                                        <?php endif; ?>
                                    ><?= $host['display_name'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <button id="submitLinked"><?= $lng['L_SEND'] ?></button>
                    </td>
                </tr>
            </table>
        </div>
        <!-- /right config column -->
    </div>
</div>
