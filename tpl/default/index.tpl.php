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
    <div id="this_system_container" class="this_system_container"></div>
    <!-- comment -->
    <div class="center_aligner_container">
        <div class="main_container">
            <!-- left -->
            <div id="left_container" class="left_container">
            </div>
            <!-- Center -->
            <div id="center_container" class="center_container">
            </div>
            <!-- Right -->
            <div id="right_container" class="right_container">
            </div>
        </div>

    </div>

</div>