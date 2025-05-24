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
            <div class="host-led <?= $tdata['host_details']['host-status'] ?>"
                alt="<?= $tdata['host_details']['title_online']?>"
                title="<?= $tdata['host_details']['title_online']?>">
            </div>
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
        <?php require __DIR__ . '/host-details-overview.tpl.php'; ?>
        <!-- /TAB1 RESUME-->
        <!-- TAB3 --><!-- NOTES -->
        <?php require __DIR__ . '/host-details-notes.tpl.php'; ?>
        <!-- /TAB3 -->
        <!-- TAB9 -->
        <?php require __DIR__ . '/host-details-logs.tpl.php'; ?>
        <!-- /TAB9 -->
        <!-- TAB10 --><!-- Graphs / PING -->
        <?php require __DIR__ . '/host-details-metrics.tpl.php'; ?>
        <!-- /TAB10 -->
        <!-- TAB11 ALARMS -->
        <?php require __DIR__ . '/host-details-alarms.tpl.php'; ?>
        <!-- /TAB11 -->
        <!-- /TAB12 --><!-- Config -->
        <?php require __DIR__ . '/host-details-config.tpl.php'; ?>
        <!-- /TAB12 -->
        <!-- TAB13 --><!-- Checks -->
        <?php require __DIR__ . '/host-details-checks.tpl.php'; ?>
        <!-- /TAB13 -->
        <!-- TAB15 --><!-- Tasks -->
        <?php require __DIR__ . '/host-details-tasks.tpl.php'; ?>
        <!-- /TAB15 -->
        <!-- TAB20 --><!-- Ansible -->
        <?php require __DIR__ . '/host-details-ansible.tpl.php'; ?>
        <!-- /TAB20 -->
        <!-- TAB16 --><!-- Agent  -->
        <?php if ($tdata['host_details']['agent_installed']) : ?>
            <?php require __DIR__ . '/host-details-agent.tpl.php'; ?>
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
