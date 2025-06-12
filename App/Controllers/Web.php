<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Core\AppContext;

use App\Services\UserService;
use App\Services\TemplateService;
use App\Pages\PageDefaults;
use App\Pages\PageHead;
use App\Pages\PageIndex;
use App\Pages\PageAuth;
use App\Pages\PageSettings;
use App\Pages\PageUser;

use App\Services\Filter;

class Web
{
    /**
     *
     * @var AppContext
     */
    private AppContext $ctx;

    /**
     *
     * @var UserService
     */
    private UserService $userService;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->userService = new UserService($ctx);
    }

    /**
     *
     * @return void
     */
    public function run(): void
    {
        $pageData = [];
        $req_page = Filter::getString('page');
        empty($req_page) ? $req_page = 'index' : null;

        if (!$this->userService->isAuthorized()) {
            $pageData = $this->get('login');
        } else {
            $pageData = $this->get($req_page);
        }

        $this->render($pageData);
    }

    /**
     *
     * @param array<string,mixed> $page_data
     *
     * @return void
     */
    public function render(array $page_data): void
    {
        $frontend = new TemplateService($this->ctx);
        $frontend->showPage($page_data);
    }

    /**
     * Obtiene los datos de la página solicitada.
     *
     * @param string $page Nombre de la página a obtener
     *
     * @return array<string,mixed> Datos de la página, combinando datos por defecto y específicos.
     */
    private function get(string $page): array
    {
        $pageDefaults = PageDefaults::getDefaults($this->ctx);
        $pageData = [];

        switch ($page) {
            case 'login':
                $pageData = PageAuth::login($this->ctx);
                break;
            case 'logout':
                PageAuth::logout($this->ctx);
                exit();
            case 'privacy':
                $pageData = PageUser::getPrivacy($this->ctx);
                break;
            case 'index':
                $pageData = PageIndex::getIndex($this->ctx);
                break;
            case 'settings':
                $pageData = PageSettings::getSettings($this->ctx);
                break;
            case 'user':
                $pageData = PageUser::getUserPage($this->ctx);
                break;
            default:
                return [];
        }

        return array_merge($pageDefaults, $pageData);
    }

    /**
     * Paginas internas.
     *
     * @return array<string,string> Funciones de páginas internas.
     */
    private function getPageFunctions(): array
    {
        return [
            'login' => 'page_login',
            'logout' => 'page_logout',
            'privacy' => 'page_privacy',
            'index' => 'page_index',
            'settings' => 'page_settings',
            'user' => 'page_user',
        ];
    }
}
