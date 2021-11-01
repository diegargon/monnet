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

<div id="hosts" class="hosts">
    <?php foreach ($tdata['hosts'] as $host) { ?>
        <div id="hosts-container" class="hosts-container">
            <a href="" rel="noreferrer" target="_blank" class="hosts-item" title="<?= $host['title'] ?>">
                <div class="hosts-thumb shadow1">
                    <img class="hosts-online" src="<?= $host['online_image'] ?>" alt="<?= $host['alt_online'] ?>"/>
                    <img class="fab" src="<?= $host['os_image'] ?>" alt="<?= $host['os_name'] ?>"/>
                    <img class="fab" src="<?= $host['img'] ?>" alt="<?= $host['title'] ?>"/>
                    <div class="hosts-title"><?= $host['title'] ?> </div>
                </div>
            </a>
        </div>
    <?php } ?>
</div>
