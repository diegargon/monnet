<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/
namespace App\Services;

use App\Core\AppContext;
use App\Core\ConfigService;
use App\Core\ModuleManager;
use App\Services\ItemsService;
use App\Services\LogSystemService;

class PageHeadService
{
    /**
     *
     * @param AppContext $ctx
     * @return array<string, mixed>
     */
    public static function getCommonHead(AppContext $ctx): array
    {
        $page = [];
        $lng = $ctx->get('lng');
        $ncfg = $ctx->get(ConfigService::class);

        $itemsService = new ItemsService($ctx);
        $search_engines = $itemsService->getByType('search_engine');

        foreach ($search_engines as $search_engine) {
            $conf = json_decode($search_engine['conf'], true);
            $page['search_engines'][] = [
                'url' => $conf['url'],
                'name' => $conf['name'],
            ];
        }
        $page['head_name'] = $ncfg->get('web_title');
        $page['load_tpl'][] = [
            'file' => 'main-center-box',
            'place' => 'head-center',
        ];

        try {
            $moduleManager = $ctx->get(ModuleManager::class);
            if ($moduleManager) {
                $moduleManager->runHook('onPageHead', [$ctx, &$page]);
            }
        } catch (\Throwable $e) {
            $logSys = $ctx->get(LogSystemService::class);
            $logSys->error('Widget error: ' . $e->getMessage());
        }
        #file_put_contents('/tmp/hookPageData.log', print_r($page, true), FILE_APPEND);

        $page['web_main']['scriptlink'][] = './scripts/jquery-2.2.4.min.js';
        $page['web_main']['scriptlink'][] = './scripts/common.js';

        // Footer
        $page['web_main']['main_footer_tpl'][] = 'footer';

        return $page;
    }
}
