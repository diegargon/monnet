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
 * @var array<string|int> $cfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
?>
<!DOCTYPE html>
<html lang="<?= $tdata['lang'] ?>">
    <head>
        <meta charset="<?= $tdata['charset'] ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
    </body>
</html>
