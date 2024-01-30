<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
?>

<div class="hosts_cat">
    <div class="categories_container">
        <?php
        foreach ($tdata['host_categories'] as $cat) {
            ?>
            <div class="category">
                <a href="refresh('host_cat', <?= $cat['on'] ?>)"><img src="/tpl/<?= $cfg['theme'] ?>/img/<?= $cat['on'] ? 'green2.png' : 'red2.png' ?>"/><span class="cat_name"><?= $cat['name'] ?></span></a>
            </div>
            <?php
        }
        ?>                        
    </div>
</div>