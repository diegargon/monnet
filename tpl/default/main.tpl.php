<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script>
            window.onload = function () {
                document.getElementById("loading_wrap").style.display = "none";
            };
            function show_loading() {
                document.getElementById("loading_wrap").style.display = "block";
            }
        </script>
    <!-- <style>* { border: 1px solid red;}</style>  -->
        <?= $tdata['main_head'] ?>
    </head>

    <body>
        <div id="loading_wrap" class="loading"></div>
        <div class="main">
            <?= $tdata['main_body'] ?>
        </div>
        <footer><?= $tdata['main_footer'] ?></footer>

    </body>
</html>