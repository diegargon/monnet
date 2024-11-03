<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Web
{
    /**
     *
     * @var AppContext
     */
    private AppContext $ctx;

    /**
     *
     * @var User
     */
    private User $user;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->user = $ctx->get('User');
    }
    /**
     *
     * @return void
     */
    public function run(): void
    {
        $pageData = [];
        $req_page = Filters::getString('page');
        empty($req_page) ? $req_page = 'index' : null;

        if (!$this->hasAccess()) {
            $pageData = $this->get('login');
        } else {
            $pageData = $this->get($req_page);
        }

        $this->render($pageData);
    }

    /**
     *
     * @param array $page_data
     * @return void
     */
    public function render(array $page_data): void
    {
        $frontend = new Frontend($this->ctx);
        $frontend->showPage($page_data);
    }

    /**
     * Check Access
     * @return bool
     */
    private function hasAccess(): bool
    {
        if (empty($this->user) || $this->user->getId() < 1) {
            return false;
        }

        return true;
    }

    /**
     * Obtiene los datos de la página solicitada.
     *
     * @param string $page Nombre de la página a obtener.
     * @return array Datos de la página, combinando datos por defecto y específicos.
     */
    private function get(string $page): array
    {
        $pageDefaults = page_defaults($this->ctx);
        $pageData = [];

        $pageFunctions = $this->getPageFunctions();
        if (array_key_exists($page, $pageFunctions)) {
            $pageFunc = $pageFunctions[$page];
            if ($page === 'logout') {
                $pageFunc($this->ctx);
                exit();
            }
            $pageData = $pageFunc($this->ctx);
        } else {
            return false;
        }


        return array_merge($pageDefaults, $pageData);
    }

    /**
     * Paginas internas.
     *
     * @return array Array de funciones de páginas internas.
     */
    private function getPageFunctions(): array
    {
        return [
            'login' => 'page_login',
            'logout' => 'page_logout',
            'privacy' => 'page_privacy',
            'index' => 'page_index',
            'settings' => 'page_settings',
        ];
    }
}
