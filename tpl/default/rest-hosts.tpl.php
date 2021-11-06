<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
//var_dump($tdata['hosts']);
?>

<div id="rest-hosts" class="hosts">
    <?php foreach ($tdata['hosts'] as $host) { ?>
        <div id="hosts-container" class="hosts-container">
            <a onclick="refresh('host-details', <?= $host['id'] ?>)" href="javascript:void(0);" rel="noreferrer" class="hosts-item" title="<?= $host['details'] ?>">
                <div class="hosts-thumb shadow1">
                    <img class="hosts-online" src="<?= $host['online_image'] ?>" alt="<?= $host['alt_online'] ?>"/>
                    <!-- <img class="fab" src="<?= $host['os_image'] ?>" alt="<?= $host['os_name'] ?>"/> -->
                    <?php if (!empty($host['img_ico'])) { ?>
                        <img class="fab" src="<?= $host['img_ico'] ?>" alt=""/>
                    <?php } ?>
                    <div class="hosts-title"><?= $host['title'] ?> </div>
                </div>
            </a>
        </div>
    <?php } ?>
</div>
