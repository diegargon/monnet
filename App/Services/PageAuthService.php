<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/
namespace App\Services;

use App\Core\AppContext;
use App\Services\UserService;

class PageAuthService
{
    public static function login(AppContext $ctx): ?array
    {
        $ncfg = $ctx->get('Config');
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
                    if (empty($ncfg->get('rel_path'))) {
                        $ncfg->set('rel_path', '/');
                    }
                    header("Location: {$ncfg->get('rel_path')} ");

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
        $ncfg = $ctx->get('Config');
        $userService = new UserService($ctx);

        $userService->logout();
        header("Location: {$ncfg->get('rel_path')}index.php");
    }
}
