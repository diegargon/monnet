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
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="header">
        <div id="head-left">
            <div class="head-left-content">
                <?= !empty($tdata['head-left']) ? $tdata['head-left'] : null; ?>
            </div>
        </div>
        <div id="head-center">
            <div class="head-center_content">
                <?= !empty($tdata['head-center']) ? $tdata['head-center'] : null; ?>
            </div>
        </div>
        <div id="head-right">
            <div class="head-right_content">
                <?= !empty($tdata['head-right']) ? $tdata['head-right'] : null; ?>
            </div>
        </div>

</div>
<!-- -->

<!-- comment -->
<div class="main_align_container">
    <div class="main_container">
        <!-- left -->
        <div id="left-container" class="left-container">
            <?= !empty($tdata['left_col_pre']) ? $tdata['left_col_pre'] : null; ?>
            <div id="host_place"></div>
            <?= !empty($tdata['left_col_post']) ? $tdata['left_col_post'] : null; ?>
        </div>
        <!-- Center -->
        <div id="center-container" class="center-container">
            <?= !empty($tdata['center_col']) ? $tdata['center_col'] : null; ?>
        </div>
        <!-- Right -->
        <div id="right-container" class="right-container">
            <?= !empty($tdata['right_col']) ? $tdata['right_col'] : null; ?>
        </div>
    </div>
    <div id="bottom_container" class="bottom_container">
        <?= !empty($tdata['bottom_col']) ? $tdata['bottom_col'] : null; ?>
    </div>
</div>
