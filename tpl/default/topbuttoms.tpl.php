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
 * @var array<int|string, mixed> $cfg Config data
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
        <a href="<?= $cfg['rel_path'] ?>?page=logout">
            <img src="tpl/<?= $cfg['theme'] ?>/img/logout.png" alt="logout" title="<?= $lng['L_LOGOUT']; ?>">
        </a>
        <a href="<?= $cfg['rel_path'] ?>?page=index">
            <img src="tpl/<?= $cfg['theme'] ?>/img/monnet.png"
                 style="border-radius:20px" alt="home" title="<?= $lng['L_HOME']; ?>"/>
        </a>
        <a href="<?= $cfg['rel_path'] ?>?page=settings">
            <img src="tpl/<?= $cfg['theme'] ?>/img/settings.png" alt="settings" title="<?= $lng['L_SETTINGS']; ?>"/>
        </a>
        <a href="<?= $cfg['rel_path'] ?>?page=<?= $privacy_page ?>">
            <img src="tpl/<?= $cfg['theme'] ?>/img/privacy.png" alt="privacy" title="<?= $lng['L_PRIVACY']; ?>"/>
        </a>
    </div>
    <?= !empty($tdata['top_button_bar']) ? $tdata['top_button_bar'] : null; ?>
</div>
