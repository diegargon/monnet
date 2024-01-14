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
<div class="bookmarks_container" id="bookmarks_container">
    <?php foreach ($tdata['bookmarks_category'] as $bookmark_category) { ?>
        <div class="bookmarks">
            <?php foreach ($bookmark_category as $bookmark) { ?>
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
    <?php } ?>
</div>