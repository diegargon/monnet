<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
return [
    'onPageHead' => function($ctx) {
        require_once __DIR__ . '/weather_widget.php';
        return weather_widget($ctx);
    },
    'onInstall' => function($ctx) {
        // ...instalación opcional...
        // $db = $ctx->get('db');
        // $db->query("CREATE TABLE ...");
    },
    'onUninstall' => function($ctx) {
        // ...desinstalación opcional...
        // $db = $ctx->get('db');
        // $db->query("DROP TABLE IF EXISTS ...");
    }
];
