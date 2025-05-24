<?php
use App\Core\ConfigService;

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
return [
    'onPageHead' => function($ctx, &$page) {
        require_once __DIR__ . '/weather_widget.php';
        $widget = weather_widget($ctx);
        if ($widget) {
            $page['web_main']['scriptlink'][] = './modules/weather_widget/weather_widget.js';
            $page['load_tpl'][] = [
                'file' => 'weather-widget',
                'place' => 'head-right',
            ];
            $page['weather_widget'] = $widget['weather_widget'] ?? $widget;
        }
    },
    'onInstall' => function($ctx) {
        // ...instalación ...
        // $db = $ctx->get('db');
        // $db->query("CREATE TABLE ...");
    },
    'onUninstall' => function($ctx) {
        // ...desinstalación ...
        // $db = $ctx->get('db');
        // $db->query("DROP TABLE IF EXISTS ...");
    },
    'onRegisterConfigCategories' => function($ctx) {
        $ncfg = $ctx->get(ConfigService::class);
        $lng = $ctx->get('lng');
        $ncfg->registerCcat(10000, $lng['L_WEATHER_WIDGET'] ?? 'Weather Widget');
    }
];
