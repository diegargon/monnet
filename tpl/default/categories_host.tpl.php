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

<div id="hosts_cat" class="hosts_cat">
    <div class="categories_container">
        <?php
        foreach ($tdata['hosts_categories'] as $cat) {
            ?>
            <div class="category">
                <a onclick="refresh('show_host_cat', <?= $cat['id'] ?>)" href="#"><img src="/tpl/<?= $cfg['theme'] ?>/img/<?= $cat['on'] ? 'green2.png' : 'red2.png' ?>"/>
                    <span class="cat_name"><?= $cat['cat_name'] ?></span>
                </a>
            </div>
            <?php
        }
        ?>                        
    </div>
</div>