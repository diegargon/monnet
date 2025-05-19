<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/
namespace App\Services;

use App\Core\AppContext;

class PageHeadService
{
    public static function getCommonHead(AppContext $ctx): array
    {
        $page = [];
        $db = $ctx->get('Mysql');
        $lng = $ctx->get('lng');
        $ncfg = $ctx->get('Config');

        $results = $db->select('items', '*', ['type' => 'search_engine']);
        $search_engines = $db->fetchAll($results);

        foreach ($search_engines as $search_engine) {
            $conf = json_decode($search_engine['conf'], true);
            $page['search_engines'][] = [
                'url' => $conf['url'],
                'name' => $conf['name'],
            ];
        }
        $page['load_tpl'][] = [
            'file' => 'main-center-box',
            'place' => 'head-center',
        ];

        // Widget y scripts
        try {
            require_once('modules/weather_widget/weather_widget.php');
            $weather = \weather_widget($ncfg, $lng);
        } catch (\Throwable $e) {
            $logSys = new LogSystemService($ctx);
            $logSys->error('Weather widget error: ' . $e->getMessage());
            $weather = null;
        }

        $page['web_main']['scriptlink'][] = './scripts/jquery-2.2.4.min.js';
        $page['web_main']['scriptlink'][] = './scripts/common.js';

        if (!empty($weather)) {
            $page['web_main']['scriptlink'][] = './modules/weather_widget/weather_widget.js';
            $page['weather_widget'] = $weather;
            $page['load_tpl'][] = [
                'file' => 'weather-widget',
                'place' => 'head-right',
            ];
        }

        // Footer
        $page['web_main']['main_footer_tpl'][] = 'footer';

        return $page;
    }
}
