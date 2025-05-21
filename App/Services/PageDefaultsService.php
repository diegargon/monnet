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

class PageDefaultsService
{
    /**
     *
     * @param AppContext $ctx
     * @return array<string, mixed>
     */
    public static function getDefaults(AppContext $ctx): array
    {
        $ncfg = $ctx->get(ConfigService::class);
        $userService = new UserService($ctx);
        $_user = $userService->getCurrentUser();

        $default_lang = $ncfg->get('default_lang', 'es');

        $page = [];
        $page['theme'] = empty($_user['theme']) ? $ncfg->get('theme') : $_user['theme'];
        $page['lang'] = empty($_user['lang']) ? $default_lang : $_user['lang'];
        $page['page_charset'] = empty($_user['page_charset']) ? $ncfg->get('default_charset') : $_user['charset'];
        $page['web_title'] = $ncfg->get('web_title');

        return $page;
    }
}
