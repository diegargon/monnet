<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/
namespace App\Pages;

use App\Core\ModuleManager;
use App\Core\AppContext;
use App\Core\Config;

class PageSettings
{
    /**
     *
     * @param AppContext $ctx
     * @return array<string, mixed>
     */
    public static function getSettings(AppContext $ctx): array
    {
        /**  Hook: Register Config Category */
        $moduleManager = $ctx->get(ModuleManager::class);
        if ($moduleManager) {
            $moduleManager->runHook('onRegisterConfigCategories', [$ctx]);
        }

        $page = [];
        $ncfg = $ctx->get(Config::class);
        $config_all = $ncfg->getAllEditable();

        $groupedConfig = [];
        foreach ($config_all as $config) {
            $ccat = $config['ccat'];
            //if ( ($json = isJson($config['cvalue']))):
            //    $config['cvalue'] = $json;
            //endif;
            $groupedConfig[$ccat][] = $config;
        }
        $page = PageHead::getCommonHead($ctx);
        $page['groupedConfig'] = $groupedConfig;
        /* Top Buttons */
        $page['load_tpl'][] = [
            'file' => 'topbuttoms',
            'place' => 'head-left',
        ];
        $page['page'] = 'index';
        $page['head_name'] = $ncfg->get('web_title');
        $page['web_main']['scriptlink'][] = './scripts/settings.js';

        $page['load_tpl'][] = [
            'file' => 'settings',
            'place' => 'left_col',
        ];

        return $page;
    }
}
