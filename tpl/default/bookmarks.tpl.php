<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="bookmarks_container" id="bookmarks_container">
    <div class="bookmarks-tabs-head-container">
        <?php
        foreach ($tdata['bookmarks_head'] as $bookmark_head) {
            $id = $bookmark_head['id'];
            if (
                    (strpos($bookmark_head['cat_name'], 'L_') === 0 ) &&
                    isset($this->lng[$bookmark_head['cat_name']])
            ) {
                $bookmark_head['cat_name'] = $lng[$bookmark_head['cat_name']];
            }
            ?>
            <button id="bookmarks_tab_<?= $id ?>" class="bookmarks-tabs-head" onclick="changeBookmarksTab('bookmark_content_tab_<?= $id ?>')"><?= $bookmark_head['cat_name'] ?></button>
            <?php
        }
        ?>
        <button id="bookmarks_tab_0" class="bookmarks-tabs-head" onclick="changeBookmarksTab('bookmark_content_tab_0')"><?= $lng['L_ALL'] ?></button>
    </div>
    <?php
    $default_active_tab = $tdata['bookmarks_default_tab'];
    if (!isset($tdata['bookmarks_default_tab'])) {
        $active = 'active';
    } else {
        $active = '';
    }

    $tdata['bookmarks_head'][] = ['id' => 0]; //To ALL tab
    foreach ($tdata['bookmarks_head'] as $bookmark_head) {
        $id = $bookmark_head['id'];
        ($id == $default_active_tab) ? $active = 'active' : null;
        ?>
        <div id="bookmark_content_tab_<?= $id ?>" class="bookmarks-tab-content bookmarks <?= $active ?>">
            <?php
            foreach ($tdata['bookmarks'] as $bookmark) {
                if ($id == 0 || $bookmark['cat_id'] == $id) {
                    ?>
                    <div class="item-container">
                                    <a href="<?= $bookmark['url'] ?>" rel="noopener noreferrer" target="_blank" class="item" title="<?= $bookmark['url'] ?>">
                                        <div class="item-thumb shadow1">
                                <img class="fab" src="<?= $bookmark['img'] ?>" alt="" style="<?= !empty($bookmark['icon_bg']) ? 'background-color: ' . $bookmark['icon_bg'] : null ?>"/>
                                <div class="item-title text_shadow_style1"><?= $bookmark['title'] ?></div>
                            </div>
                        </a>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <?php
        $active = '';
    }
    ?>

</div>