<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="header">
    <div class="head_align_center">
        <div id="head_left"><div class="head_left_content"><?= !empty($tdata['head_left']) ? $tdata['head_left'] : null; ?></div></div>
        <div id="head_center">
            <div class="head_center_content">
                <?= !empty($tdata['head_center']) ? $tdata['head_center'] : null; ?>
            </div>
        </div>
        <div id="head_right"><div class="head_right_content"><?= !empty($tdata['head_right']) ? $tdata['head_right'] : null; ?></div></div>
    </div>
</div>
<!-- -->
