<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

Class Web {

    private AppCtx $ctx;
    private array $lng;
    private array $cfg;
    private User $user;

    public function __construct(AppCtx $ctx) {
        $this->ctx = $ctx;
        $this->cfg = $ctx->getAppCfg();
        $this->lng = $ctx->getAppLang();
        $this->user = $ctx->getAppUser();
    }

    public function run() {
        $page = $this->ValidateRequestPage();
        if (!$page) {
            exit('Fail: Validate requested page');
        }
        $page_data = $this->getPageData($page);
        $this->render($page_data);
    }

    private function ValidateRequestPage() {
        $req_page = Filters::getString('page');
        empty($req_page) ? $req_page = 'index' : null;
        empty($this->user) || $this->user->getId() < 1 ? $req_page = 'login' : null;

        //echo $this->user->getId();

        $valid_pages = ['index', 'login', 'logout', 'privacy', 'settings'];

        (!isset($req_page) || $req_page == '') ? $req_page = 'index' : null;

        if (in_array($req_page, $valid_pages)) {
            return $req_page;
        }

        return false;
    }

    function getPageData(string $page) {
        $page_func = 'page_' . $page;

        $page_defaults = [];
        $page_data = [];

        $page_defaults = page_defaults($this->ctx);
        //$page_data = $page_func($this->cfg, $this->lng, $this->user);
        if ($page == 'login') {
            $page_data = page_login($this->ctx);
        } else if ($page == 'logout') {
            $page_data = page_logout($this->ctx);
        } else if ($page === 'privacy') {
            $page_data = page_privacy($this->ctx);
        } else if ($page === 'index') {
            $page_data = page_index($this->ctx);
        } else if ($page === 'settings') {
            $page_data = page_settings($this->ctx);
        }

        return array_merge($page_defaults, $page_data);
    }

    function render(array $page_data) {
        $frontend = new Frontend($this->cfg, $this->lng);
        $frontend->showPage($page_data);
    }
}
