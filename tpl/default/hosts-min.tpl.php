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
!defined('IN_WEB') ? exit : true;
//var_dump($tdata);
if (empty($tdata['hosts'])) {
    return [];
}
?>

<div id="<?= $tdata['container-id'] ?>" class="hosts">
    <div class="hosts-head"><?= $tdata['head-title'] ?></div>
    <?php foreach ($tdata['hosts'] as $host) { ?>
        <div id="hosts-container" class="hosts-container">
            <a onclick="submitCommand('host-details', {id: <?= $host['id'] ?>})"
               href="javascript:void(0);" rel="noreferrer" class="hosts-item" title="<?= $host['details'] ?>">
                <div class="hosts-thumb shadow1 <?= $host['glow'] ?> ">
                    <img class="hosts-online" src="<?= $host['online_image'] ?>"
                         alt="online_status" title="<?= $host['title_online'] ?>"/>
                <?php if (!empty($host['alert_mark'])) : ?>
                    <img class="hosts-online" src="<?= $host['alert_mark'] ?>"
                        alt="online_status" title="<?= $host['alert_msg'] ?>"/>
                <?php endif; ?>
                <?php if (!empty($host['warn_mark']) && empty($host['alert_mark'])) : ?>
                    <img class="hosts-online" src="<?= $host['warn_mark'] ?>"
                        alt="online_status" title="<?= $host['warn_msg'] ?>"/>
                <?php endif; ?>

                <?php if (!empty($host['system_type_image'])) : ?>
                    <img class="fab" src="<?= $host['system_type_image'] ?>" alt="system_img"
                        title="<?= $host['system_type_name'] ?>"/>
                <?php endif; ?>
                    <div class="hosts-title text_shadow_style2"><?= $host['display_name'] ?> </div>
                    <div class="min-details-hidden">
                    <?php if (!empty($host['manufacture_image'])) : ?>
                        <img class="fab" src="<?= $host['manufacture_image'] ?>"
                            alt="os_img" title="<?= $host['manufacture_name'] ?>"/>
                    <?php endif; ?>
                    <?php if (!empty($host['os_image'])) : ?>
                        <img class="fab" src="<?= $host['os_image'] ?>"
                            alt="os_img" title="<?= $host['os_name'] ?>"/>
                    <?php endif; ?>
                    <?php if (!empty($host['access_link'])) : ?>
                        <a href="<?= $host['access_link'] ?>">
                        <img class="fab" src="tpl/<?= $cfg['theme']?>/img/icons/link.png"
                            alt="access_link" title="<?= $host['access_link'] ?>"/>
                        </a>
                    <?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
    <?php } ?>
</div>
