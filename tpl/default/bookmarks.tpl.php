<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var Config $ncfg
 * @var array<string> $lng Language data
 * @var array<mixed> $tdata Template Data
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="bookmarks-container" id="bookmarks-container">
    <div class="bookmarks-tabs-head-container">
        <?php
        foreach ($tdata['bookmarks_head'] as $bookmark_head) {
            $id = $bookmark_head['id'];
            ?>
            <button id="bookmarks_tab_<?= $id ?>" class="bookmarks-tabs-head"
                    onclick="changeBookmarksTab(<?= $id ?>)">
                        <?= $bookmark_head['cat_name'] ?>
                <input onclick="confirmSubmit('removeBookmarkCat', {id: <?= $id ?>})"
                    type="image" class="delete_cat_btn action-icon-tab" src="tpl/default/img/remove.png"
                    alt="<?= $lng['L_DELETE'] ?>" title="<?= $lng['L_DELETE'] ?>">
            </button>
            <?php
        }
        ?>
        <button id="bookmarks_tab_0" class="bookmarks-tabs-head"
                onclick="changeBookmarksTab(0)"><?= $lng['L_ALL'] ?></button>
        <button id="bookmarks_tab_add" class="bookmarks-tabs-head add_cat_btn"
                onclick="addBookmarkCat('<?= $lng['L_ADD_BOOKMARKS_CAT'] ?>')">+</button>
    </div>
    <?php
    $default_active_tab = $tdata['bookmarks_default_tab'];
    if (!isset($tdata['bookmarks_default_tab'])) {
        $active = 'active';
    } else {
        $active = '';
    }

    $tdata['bookmarks_head'][] = ['id' => 0]; //For ALL tab
    foreach ($tdata['bookmarks_head'] as $bookmark_head) {
        $id = $bookmark_head['id'];
        $active = '';
        ($id == $default_active_tab) ? $active = 'active' : null;
        ?>
        <div id="bookmark_content_tab_<?= $id ?>" class="bookmarks-tab-content bookmarks <?= $active ?>">
        <?php
        foreach ($tdata['bookmarks'] as $bookmark) {
            if ($id == 0 || $bookmark['cat_id'] == $id) {
                ?>
                <div id="item_num_<?= $bookmark['id'] ?>" class="item-container">
                    <div class="delete_bookmark">
                        <input onclick="confirmSubmit('removeBookmark',{id: <?= $bookmark['id'] ?>})" type="image"
                            class="action-icon remove" src="tpl/default/img/remove.png" alt="Delete"
                            title="Borrar">
                    </div>
                    <div class="edit_bookmark">
                        <input onclick="submitCommand('mgmtBookmark',{id: <?= $bookmark['id'] ?>, action: 'edit'})"
                            type="image" class="action-icon remove" src="tpl/default/img/edit.png"
                            alt="Edit" title="Borrar">
                    </div>
                    <a href="<?= $bookmark['url'] ?>" rel="noopener noreferrer" target="_blank"
                        class="item_link" title="<?= $bookmark['url'] ?>">
                        <div class="item-thumb shadow1">
                            <img class="fab" src="<?= $bookmark['img'] ?>" alt=""
                                style="<?=
                                            !empty($bookmark['icon_bg']) ? 'background-color: ' .
                                            $bookmark['icon_bg'] : null
                                    ?>"/>
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
    }
    ?>
</div>
