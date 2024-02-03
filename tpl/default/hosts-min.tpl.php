<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

if (empty($tdata['hosts'])) {
    return [];
}
?>

<div id="<?= $tdata['container-id'] ?>" class="hosts">
    <div class="hosts-head"><?= $tdata['head-title'] ?></div>
    <?php foreach ($tdata['hosts'] as $host) { ?>
        <div id="hosts-container" class="hosts-container">
            <a onclick="refresh('host-details', <?= $host['id'] ?>)" href="javascript:void(0);" rel="noreferrer" class="hosts-item" title="<?= $host['details'] ?>">
                <div class="hosts-thumb shadow1">
                    <img class="hosts-online" src="<?= $host['online_image'] ?>" alt="online_status" title="<?= $host['title_online'] ?>"/>
                    <?php if (!empty($host['warn_mark']) && $host['online'] == 1) { ?>
                        <img class="hosts-online" src="<?= $host['warn_mark'] ?>" alt="online_status" title="<?= $host['warn_msg'] ?>"/>
                    <?php } ?>
                    <?php if (!empty($host['os_img'])) { ?>
                        <img class="fab" src="<?= $host['os_image'] ?>" alt="os_img" title="<?= $host['os'] ?>"/>
                    <?php } ?>
                    <?php if (!empty($host['os_distribution_image'])) { ?>
                        <img class="fab" src="<?= $host['os_distribution_image'] ?>" alt="distribution_img" title="<?= $host['os_distribution'] ?>"/>
                    <?php } ?>              
                    <?php if (!empty($host['system_image'])) { ?>
                        <img class="fab" src="<?= $host['system_image'] ?>" alt="system_img" title="<?= $host['system'] ?>"/>
                    <?php } ?>                                   
                    <div class="hosts-title text_shadow_style2"><?= $host['title'] ?> </div>
                </div>
            </a>
        </div>
    <?php } ?>
</div>
