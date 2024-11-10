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
 * @var array<int|string, mixed> $cfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>
<div id="mgmt-bookmark-container" class="draggable">
    <div class="front-container-bar dragbar">
        <button id="close_mgmtbookmark" class="button-ctrl" type="submit">
            <img class="close_link" src="./tpl/<?= $cfg['theme'] ?>/img/close.png" title="<?= $lng['L_CLOSE'] ?>">
        </button>
        <div class="front-container-bar-title"><?= $lng['L_EDIT'] . ' ' . $lng['L_BOOKMARK'] ?></div>
    </div>
    <div class="form_container">
        <div id="status_msg"><?= $tdata['status_msg'] ?? '' ?></div>
        <div id="error_msg"><?= $tdata['error_msg'] ?? '' ?></div>
        <input type="hidden" name="mgmtBookmarkForm" value="1" readonly/>
        <input type="hidden" id="bookmark_id" name="bookmark_id" value="<?= $tdata['id'] ?? '' ?>" readonly=""/>
        <br/>
        <label for="bookmarkName"><?= $lng['L_NAME'] ?>:</label>
        <input type="text" id="bookmarkName"
               name="bookmarkName" size="12" maxlength="12" required
               value="<?= $tdata['title'] ?? '' ?>">
        <br/>
        <label for="cat_id"><?= $lng['L_CATEGORY'] ?>:</label>
        <select id="cat_id" name="cat_id" required>
            <?php foreach ($tdata['web_categories'] as $cat) : ?>
                <?php $cat_name = isset($lng[$cat['cat_name']]) ? $lng[$cat['cat_name']] : $cat['cat_name']; ?>
                <option value="<?= $cat['id'] ?>"><?= $cat_name ?></option>
            <?php endforeach; ?>
        </select>
        <br/>
        <label for="urlip"><?= $lng['L_URLIP'] ?>:</label>
        <input type="text" id="urlip" name="urlip" size="32" maxlength="450" required
               value="<?= $tdata['url'] ?? '' ?>">
        <br/>
        <label for="image_type"><?= $lng['L_IMAGE_TYPE'] ?>:</label>
        <select id="image_type" name="image_type">
            <option value="local_img"><?= $lng['L_LOCAL_IMAGE'] ?></option>
            <option value="image_resource"><?= $lng['L_LINK'] ?></option>
        </select>
        <br/>
        <div class="image-dropdown">
            <select onchange="updateThumbnail(this)">
                <option value="">Selecciona una imagen...</option>
                     <?php foreach ($tdata['local_icons'] as $image): ?>
                        <option value="<?= $image['path']; ?>" data-thumbnail="<?= $image['path']; ?>">
                            <?= $image['basename']; ?>
                        </option>
                    <?php endforeach; ?>
            </select>
        </div>
        <div class="thumbnail-preview">
            <img id="thumbnail" src="" alt="Miniatura" style="display: none;">
            <span id="imageName"></span>
        </div>
        <label for="field_img"><?= $lng['L_LINK_HELP'] ?>:</label>
        <input type="text" id="field_img" name="field_img" size="32" maxlength="450"
               value="<?= $tdata['image_resource'] ?? '' ?>">
        <br/>
        <label for="weight"><?= $lng['L_WEIGHT'] ?>:</label>
        <select id="weight" name="weight" required>
            <?php for ($i = 0; $i <= 90; $i += 10) : ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
        </select>
        <br/>
        <!-- Botón para enviar el formulario -->
        <button id="updateBookmark" type="submit"><?= $lng['L_SEND'] ?></button>
    </div>
</div>
