<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 * @version 0.6
 */
/**
 * In frontend->getTpl()
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
?>
<!DOCTYPE html>
<html lang="<?= $tdata['lang'] ?>" dir="<?= $tdata['dir'] ?? 'ltr' ?>">
    <head>
        <meta charset="<?= $tdata['web_charset'] ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="<?= $tdata['web_description'] ?? 'Web' ?>">
        <meta name="keywords" content="<?= $tdata['web_keywords'] ?? '' ?>">
        <?= $tdata['meta_extra'] ?? '' ?>
        <link rel="shortcut icon" href="favicon.ico" />
        <meta name="referrer" content="never">
        <title><?= $tdata['web_title'] ?></title>
        <?= $tdata['main_head'] ?>
    <!-- <style>* { border: 1px solid red;}</style>  -->
    </head>

    <body>
        <div id="loading_wrap" class="loading"></div>
        <div class="main">
            <?= $tdata['main_body'] ?>
        </div>
        <footer><?= $tdata['main_footer'] ?></footer>
        <?= $tdata['footer_script'] ?? '' ?>
    </body>
</html>
