<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="header">
    <div class="search_container">
        <h1 class='title gradiant'><a href=""><?= $tdata['head_name'] ?></a></h1>
        <div class="search-wrapper">
            <form target="_blank"  action="<?= $tdata['search_engines'][0]['url'] ?>" method="GET">
                <input type="text" name="<?= $tdata['search_engines'][0]['name'] ?>" required class="search-box" placeholder="" autofocus/>
                <button class="close-icon" type="reset"></button>
            </form>
        </div>
    </div>
    <!-- -->
    <div id="top_container" class="top_container"></div>
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

</div>