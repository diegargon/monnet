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
 * @var Config data
 * @var array<string> $lng Language data
 * @var array<mixed> $tdata Template Data
 */
$privacy_page = 'privacy';
if (Filters::getString('page') == $privacy_page) {
    $privacy_page = 'index';
}
?>
<div id="top_button_bar" class="top_button_bar">
    <div class="bar_button_container">
        <a href="<?= $ncfg->get('rel_path') ?>?page=logout">
            <img src="tpl/<?= $ncfg->get('theme') ?>/img/logout.png" alt="logout" title="<?= $lng['L_LOGOUT']; ?>">
        </a>
        <a href="<?= $ncfg->get('rel_path') ?>?page=<?= $privacy_page ?>">
            <img src="tpl/<?= $ncfg->get('theme') ?>/img/privacy.png" alt="privacy" title="<?= $lng['L_PRIVACY']; ?>"/>
        </a>
    </div>
    <?= !empty($tdata['top_button_bar']) ? $tdata['top_button_bar'] : null; ?>
</div>
