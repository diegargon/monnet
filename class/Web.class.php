<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

Class Web {

    private Database $db;
    private array $lng;
    private array $cfg;
    private User $user;

    public function __construct($cfg, $db, $user, $lng) {
        $this->cfg = $cfg;
        $this->db = $db;
        $this->user = $user;
        $this->lng = $lng;
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

        $valid_pages = ['index', 'login'];

        (!isset($req_page) || $req_page == '') ? $req_page = 'index' : null;

        if (in_array($req_page, $valid_pages)) {
            return $req_page;
        }

        return false;
    }

    function getPageData($page) {
        $page_func = 'page_' . $page;

        $page_defaults = [];
        $page_data = [];

        $page_defaults = page_defaults($this->cfg, $this->user);
        //$page_data = $page_func($this->cfg, $this->lng, $this->user);
        if ($page == 'login') {
            $page_data = page_login($this->cfg, $this->lng, $this->user);
        } else if ($page == 'logout') {
            //TODO
        } else if ($page === 'index') {
            $page_data = page_index($this->cfg, $this->db, $this->lng);
        }

        return array_merge($page_defaults, $page_data);
    }

    function render($page_data) {
        $frontend = new Frontend($this->cfg);
        $frontend->showPage($page_data);
    }

}
