<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<string> $cfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>

<div id="hosts_cat" class="hosts_cat">
    <div class="categories_container">
        <div class="category dropdown">
            <img src="/tpl/<?= $cfg['theme'] ?>/img/network.png"/>
            <div class="dropdown-content" id="myDropdown">
                <?php
                if (!empty($tdata['networks']) && valid_array($tdata['networks'])) {
                    ($tdata['networks_selected']) === 1 ? $disabled = ' disabled ' : $disabled = '';
                    foreach ($tdata['networks'] as $net) {
                        $netid = $net['id'];
                        if (!empty($net['selected'])) {
                            $check_opt = ' checked ' . $disabled;
                        } else {
                            $check_opt = '';
                        }
                        ?>
                        <input type="checkbox" id="option_network_<?= $netid ?>" class="option_network"
                               name="option1" value="<?= $netid ?>" <?= $check_opt ?>/>
                        <label for="option_network_<?= $netid ?>"><?= $net['name'] ?></label><br>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <?php
        foreach ($tdata['hosts_categories'] as $cat) {
            ?>

            <div class="category">
                <a class="show_host_cat"  data-catid="<?= $cat['id'] ?>" href="#">
                    <img src="/tpl/<?= $cfg['theme'] ?>/img/<?= $cat['on'] ? 'green.png' : 'red.png' ?>"/>
                    <input onclick="confirmRefresh('removeHostsCat',<?= $cat['id'] ?>)" type="image"
                           class="delete_cat_btn action-icon-tab" src="tpl/default/img/remove.png"
                           alt="<?= $lng['L_DELETE'] ?>" title="<?= $lng['L_DELETE'] ?>">
                    <span class="text_shadow_style1 cat_name"><?= $cat['cat_name'] ?></span>
                </a>
            </div>
            <?php
        }
        ?>
        <input onclick="addHostsCat('<?= $lng['L_ADD_HOST_CAT'] ?>')" type="image" class="add_cat_btn action-icon-tab"
               src="tpl/default/img/add.png" alt="<?= $lng['L_ADD'] ?>" title="<?= $lng['L_ADD'] ?>">
    </div>
</div>
