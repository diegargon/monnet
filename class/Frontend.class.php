<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Frontend {

    private $cfg;

    public function __construct($cfg) {
        $this->cfg = $cfg;
    }

    function showPage($tdata) {
        $web['css'] = $this->getCssFile($this->cfg['theme'], $this->cfg['css']);
        $web['body'] = $this->getTpl($tdata['page'], $tdata);
        $web['footer'] = '';
        echo $this->getTpl('main', array_merge($tdata, $web));
    }

    function getTpl(string $tpl, array $tdata = []) {

        ob_start();
        $tpl_file = 'tpl/' . $tdata['theme'] . '/' . $tpl . '.tpl.php';
        !file_exists($tpl_file) ? $tpl_file = 'tpl/default/' . $tpl . '.tpl.php' : null;
        include($tpl_file);

        return ob_get_clean();
    }

    function getCssFile(string $theme, string $css) {
        $css_file = 'tpl/' . $theme . '/css/' . $css . '.css';
        !file_exists($css_file) ? $css_file = 'tpl/default/css/default.css' : null;
        $css_file .= '?nocache=' . time(); //TODO: To Remove: avoid cache css while dev

        return $css_file;
    }

    function msgBox(array $msg) {
        global $LNG;

        (substr($msg['title'], 0, 2) == 'L_') ? $msg['title'] = $LNG[$msg['title']] : null;
        (substr($msg['body'], 0, 2) == 'L_') ? $msg['body'] = $LNG[$msg['body']] : null;
        return $this->getTpl('msgbox', $msg);
    }

    function msgPage(array $msg) {
        global $cfg;

        $footer = $this->getFooter();
        $menu = $this->getMenu();
        $body = $this->msgBox(['title' => $msg['title'], 'body' => $msg['body']]);
        $tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
        $tdata['css_file'] = $this->getCssFile($cfg['theme'], $cfg['css']);
        echo $this->getTpl('html_mstruct', $tdata);

        exit();
    }

    function getMenu() {
        global $prefs, $cfg;

        if (isset($_GET['sw_opt'])) {
            $value = $prefs->getPrefsItem('hide_opt');
            if ($value == 0) {
                $prefs->setPrefsItem('hide_opt', 1);
            } else {
                $prefs->setPrefsItem('hide_opt', 0);
            }
        }

        if (!empty(Filter::getString('page'))) {
            $page = Filter::getString('page');
            $tdata['menu_opt_link'] = str_replace('&sw_opt=1', '', basename($_SERVER['REQUEST_URI'])) . '&sw_opt=1';
        } else {
            $tdata['menu_opt_link'] = "?page=index&sw_opt=1";
            if (!empty($cfg['index_page'])) {
                $page = $cfg['index_page'];
            } else {
                $page = 'index';
            }
        }

        if (empty($prefs->getPrefsItem('hide_opt'))) {
            $tdata['menu_opt'] = $this->getMenuOptions();
            $tdata['arrow'] = '&uarr;';
        } else {
            $tdata['arrow'] = '&darr;';
        }

        $tdata['page'] = $page;
        return $this->getTpl('menu', $tdata);
    }

    function getFooter() {
        global $db, $cfg;

        $cfg['show_querys'] ?? 0;
        $querys = $db->getQuerys();
        valid_array($querys) ? $num_querys = count($querys) : $num_querys = 0;
        $tdata['num_querys'] = $num_querys;
        $cfg['show_querys'] ? $tdata['querys'] = $querys : null;

        return $this->getTpl('footer', $tdata);
    }

}
