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

class PageAuthService
{
    public static function login(AppContext $ctx): ?array
    {
        $ncfg = $ctx->get(ConfigService::class);
        $lng = $ctx->get('lng');

        if (
            !empty($_SERVER['REQUEST_METHOD']) &&
            $_SERVER['REQUEST_METHOD'] == 'POST'
        ) {

            $userService = new UserService($ctx);

            $username = Filter::postUsername('username');
            $password = Filter::postPassword('password');

            if (!empty($username) && !empty($password)) {
                $user = $userService->login($username, $password);
                if (!empty($user['id']) && $user['id'] > 0) {
                    $basePath = dirname($_SERVER['SCRIPT_NAME']);
                    if ($basePath !== '/' && substr($basePath, -1) !== '/') {
                        $basePath .= '/';
                    }
                    header("Location: {$basePath}");
                    exit();
                }
            }
        }
        # Show login page
        $page['head_name'] = $ncfg->get('web_title');
        $page['web_main']['scriptlink'][] = './scripts/jquery-2.2.4.min.js';
        $page['web_main']['scriptlink'][] = './scripts/background.js';

        $page['page'] = 'login';
        $page['tpl'] = 'login';
        $page['log_in'] = $lng['L_LOGIN'];

        # TODO FILTER
        if (isset($_COOKIE['username'])) {
            $page['username'] = htmlspecialchars($_COOKIE['username']);
        } else {
            $page['username'] = '';
        }

        $page['username_placeholder'] = $lng['L_USERNAME'];
        $page['password_placeholder'] = $lng['L_PASSWORD'];
        if (!empty($page['username'])) {
            $page['set_pass_focus'] = 1;
        } else {
            $page['set_username_focus'] = 1;
        }

        return $page;
    }

    public static function logout(AppContext $ctx): void
    {
        $userService = new UserService($ctx);
        $userService->logout();
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && substr($basePath, -1) !== '/') {
            $basePath .= '/';
        }
        header("Location: {$basePath}");
    }
}
