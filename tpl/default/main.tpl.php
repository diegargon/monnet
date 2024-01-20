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
        <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
        <!-- Charts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>        
        <!-- /Charts -->
        <script>
            window.onload = function () {
                document.getElementById("loading_wrap").style.display = "none";
            };
            function show_loading() {
                document.getElementById("loading_wrap").style.display = "block";
            };
            function changeTab(tabId) {
                // Ocultar todos los contenidos de las pestañas
                const tabContents = document.querySelectorAll('.host-details-tab-content');
                tabContents.forEach(tabContent => tabContent.classList.remove('active'));

                // Resaltar el botón de la pestaña seleccionada
                const tabs = document.querySelectorAll('.host-details-tabs-head');
                tabs.forEach(tab => tab.classList.remove('active'));

                // Mostrar el contenido de la pestaña seleccionada
                const selectedTabContent = document.getElementById(tabId);
                selectedTabContent.classList.add('active');

                // Resaltar el botón de la pestaña seleccionada
                const selectedTab = document.querySelector(`button[onclick="changeTab('${tabId}')"]`);
                selectedTab.classList.add('active');
            };
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