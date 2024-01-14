<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2023 Diego Garcia (diego/@/envigo.net)
 */
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

<div id="top_container_bar" class="top_container_bar">
    <div class="bar_item_container">
        <a href="<?= $cfg['rel_path']?>?page=logout">
            <img src="tpl/<?= $cfg['theme'] ?>/img/icons/logout.png" alt="logout"/>
        </a>
    </div>
    <?= !empty($tdata['top_container_bar']) ? $tdata['top_container_bar'] : null; ?>
</div>        

<!-- comment -->
<div class="center_aligner_container">
    <div class="main_container">
        <!-- left -->
        <div id="left_container" class="left_container">
            <?= !empty($tdata['left_col']) ? $tdata['left_col'] : null; ?>
        </div>
        <!-- Center -->
        <div id="center_container" class="center_container">
            <?= !empty($tdata['center_col']) ? $tdata['center_col'] : null; ?>
        </div>
        <!-- Right -->
        <div id="right_container" class="right_container">
            <?= !empty($tdata['right_col']) ? $tdata['right_col'] : null; ?>
        </div>
    </div>
    <div id="bottom_container" class="bottom_container">
        <?= !empty($tdata['bottom_col']) ? $tdata['bottom_col'] : null; ?>
    </div>
</div>