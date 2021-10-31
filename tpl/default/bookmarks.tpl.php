<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="bookmarks_container" id="bookmarks_container">
    <div class="bookmarks">
        <?php foreach ($tdata['bookmarks'] as $bookmark) { ?>

            <div class="item-container">
                <a href="<?= $bookmark['url'] ?>" rel="noreferrer" target="_blank" class="item" title="<?= $bookmark['url'] ?>">
                    <div class="item-thumb shadow1">
                        <img class="fab" src="<?= $bookmark['img'] ?>" alt="" style="<?= !empty($bookmark['icon_bg']) ? 'background-color: ' . $bookmark['icon_bg'] : null ?>"/>
                        <div class="item-title"><?= $bookmark['title'] ?></div>
                    </div>
                </a>
            </div>
        <?php } ?>
    </div>
</div>