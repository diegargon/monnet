<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var array<string> $cfg
 */
/**
 * @var array<string> $lng
 */
/**
 * @var array<mixed> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>
<div id="add-bookmark-container" class="draggable">
    <div class="front-container-bar dragbar">
        <button id="close_addbookmark" class="button-ctrl" type="submit">
            <img class="close_link" src="./tpl/<?= $cfg['theme'] ?>/img/close.png" title="<?= $lng['L_CLOSE'] ?>">
        </button>
        <div class="front-container-bar-title"><?= $lng['L_ADD'] . ' ' . $lng['L_BOOKMARK'] ?></div>
    </div>
    <div class="form_container">
        <div id="status_msg"><?= isset($tdata['status_msg']) ? $tdata['status_msg'] : null ?></div>
        <div id="error_msg"><?= isset($tdata['error_msg']) ? $tdata['error_msg'] : null ?></div>
        <input type="hidden"  name="addBookmarkForm" value="1" readonly/>
        <br/>
        <label for="bookmarkName"><?= $lng['L_NAME'] ?>:</label>
        <input type="text" id="bookmarkName"
               name="bookmarkName" size="12" maxlength="12" required
               value="<?= !empty($tdata['bookmarkName']) ? $tdata['bookmarkName'] : null ?>">
        <br/>
        <label for="cat_id"><?= $lng['L_CATEGORY'] ?>:</label>
        <select id="cat_id" name="cat_id" required>
            <?php foreach ($tdata['webs_categories'] as $cat): ?>
                <?php $cat_name = isset($lng[$cat['cat_name']]) ? $lng[$cat['cat_name']] : $cat['cat_name']; ?>
                <option value="<?= $cat['id'] ?>"><?= $cat_name ?></option>
            <?php endforeach; ?>
        </select>
        <br/>
        <label for="urlip"><?= $lng['L_URLIP'] ?>:</label>
        <input type="text" id="urlip" name="urlip" size="32" maxlength="450" required
               value="<?= !empty($tdata['urlip']) ? $tdata['urlip'] : null ?>">
        <br/>
        <label for="image_type"><?= $lng['L_IMAGE_TYPE'] ?>:</label>
        <select id="image_type" name="image_type">
            <option value="local_img"><?= $lng['L_LOCAL_IMAGE'] ?></option>
            <option value="favicon">favicon.ico</option>
            <option value="image_resource"><?= $lng['L_LINK'] ?></option>
        </select>
        <br/>
        <label for="field_img"><?= $lng['L_LINK_HELP'] ?>:</label>
        <input type="text" id="field_img" name="field_img" size="32" maxlength="450"
               value="<?= !empty($tdata['field_img']) ? $tdata['field_img'] : null ?>">
        <br/>
        <label for="weight"><?= $lng['L_WEIGHT'] ?>:</label>
        <select id="weight" name="weight" required>
            <?php for ($i = 0; $i <= 90; $i += 10): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
        </select>
        <br/>
        <!-- Botón para enviar el formulario -->
        <button id="submitBookmark" type="submit"/><?= $lng['L_ADD'] ?></button>
    </div>
</div>
