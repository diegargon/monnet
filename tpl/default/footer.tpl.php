<?php
/**
*
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 *
 */
/**
 * In frontend->getTpl()
 * @var array<int|string, mixed> $cfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="footer_bar">
    <div class="footer_right_element">
        <a href="<?= $cfg['monnet_homepage'] ?>"
           target="_blank">v<?= $cfg['monnet_version'] ?>.<?= $cfg['monnet_revision'] ?>
        </a>
    </div>
    <div class="clearfix"></div>
</div>
