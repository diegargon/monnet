<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2023 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="search_container">
    <h1 class='title gradiant'><a href=""><?= $tdata['head_name'] ?></a></h1>
    <div class="search-wrapper">
        <form target="_blank"  action="<?= $tdata['search_engines'][0]['url'] ?>" method="GET">
            <input type="text" name="<?= $tdata['search_engines'][0]['name'] ?>" required class="search-box" placeholder="Google" autofocus/>
            <button class="close-icon" type="reset"></button>
        </form>
    </div>
</div>