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

use App\Services\UserService;

class PageUserService
{
    /**
     *
     * @param AppContext $ctx
     * @return array<string, mixed>
     */
    public static function getUserPage(AppContext $ctx): array
    {
        $ncfg = $ctx->get(ConfigService::class);
        $page = [];
        $userService = new UserService($ctx);
        $user = $userService->getCurrentUser();

        $page = PageHeadService::getCommonHead($ctx);

        /* Top Buttons */
        $page['load_tpl'][] = [
            'file' => 'topbuttoms',
            'place' => 'head-left',
        ];

        $page['page'] = 'index';
        $page['head_name'] = $ncfg->get('web_title');
        $page['web_main']['scriptlink'][] = './scripts/user-mgmt.js';

        $page['user'] = $user;
        $page['load_tpl'][] = [
            'file' => 'user',
            'place' => 'left_col_pre',
        ];
        $page['load_tpl'][] = [
            'file' => 'user-mgmt',
            'place' => 'right_col',
        ];
        return $page;
    }

    /**
     *
     * @param AppContext $ctx
     * @return array<string,string>
     */
    public static function getPrivacy(AppContext $ctx): array
    {
        $page = [];

        $ncfg = $ctx->get(ConfigService::class);

        $page = PageHeadService::getCommonHead($ctx);
        /* Top Buttons */
        $page['load_tpl'][] = [
            'file' => 'topbuttoms',
            'place' => 'head-left',
        ];

        $page['page'] = 'index';
        $page['head_name'] = $ncfg->get('web_title');
        $page['web_main']['scriptlink'][] = './scripts/jquery-2.2.4.min.js';
        $page['web_main']['scriptlink'][] = './scripts/background.js';

        return $page;
    }
}
