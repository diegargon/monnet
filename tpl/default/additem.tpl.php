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
<div id="add-bookmark-container" style="<?= isset($tdata['show_add_bookmark']) ? 'display:block;' : null ?>">
    <div class="front-container-bar">
        <button id="close_addbookmark" class="button-ctrl" type="submit"><img class="closelink" src="./tpl/<?= $cfg['theme'] ?>/img/close.png" title="<?= $lng['L_CLOSE'] ?>"></button> 
    </div>
    <div class="form_container"> 
        <div id="status_msg"><?= isset($tdata['status_msg']) ? $tdata['status_msg'] : null ?></div>
        <div id="error_msg"><?= isset($tdata['error_msg']) ? $tdata['error_msg'] : null ?></div>
        <form id="addBookmarkForm" method="POST">
            <input type="hidden"  name="addBookmarkForm" value="1" readonly/>
            <label for="bookmarkName"><?= $lng['L_NAME'] ?>:</label>
            <input type="text" id="bookmarkName" name="bookmarkName"  size="12" maxlength="12" required value="<?= !empty($tdata['bookmarkName']) ? $tdata['bookmarkName'] : null ?>">

            <label for="host"><?= $lng['L_TYPE'] ?>:</label>            
            <select id="url_type" name="url_type" required>                 
                <?php
                foreach ($tdata['items_categories'] as $cat) {
                    if (isset($lng[$cat['cat_name']])) {
                        $cat_name = $lng[$cat['cat_name']];
                    } else {
                        $cat_name = $cat['cat_name'];
                    }
                    ?>
                    <option value="<?= $cat['id'] ?>"><?= $cat_name ?></option>
                    <?php
                }
                ?>
            </select>

            <label for="url">URL/IP:</label>
            <input type="text" id="urlip" name="urlip" size="32" maxlength="250" required value="<?= !empty($tdata['urlip']) ? $tdata['urlip'] : null ?>">

            <label for="image_type"><?= $lng['L_IMAGE_TYPE'] ?>:</label>
            <select id="image_type" name="image_type">
                <option value="local_img"><?= $lng['L_LOCAL_IMAGE'] ?></option>
                <option value="favicon">favicon.ico</option>
                <option value="image_resource"><?= $lng['L_LINK'] ?></option>
            </select>
            <br/>

            <label for="field_img"><?= $lng['L_LINK'] ?>:</label>
            <input type="text" id="field_img" name="field_img" size="32" maxlength="250" value="<?= !empty($tdata['field_img']) ? $tdata['field_img'] : null ?>">

            <label for="weight"><?= $lng['L_WEIGHT'] ?>:</label>
            <select id="weight" name="weight" required>
                <option value="0">0</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
                <option value="40">40</option>
                <option value="50">50</option>
                <option value="60">60</option>
                <option value="70">70</option>
                <option value="80">80</option>
                <option value="90">90</option>
            </select>            

            <!-- Botón para enviar el formulario -->
            <input type="submit" value="<?= $lng['L_ADD'] ?>"/>
        </form>
    </div>
</div>

