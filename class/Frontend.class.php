<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2023 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Frontend {

    private $cfg;
    private $lng;

    public function __construct(array $cfg, array $lng) {
        $this->cfg = $cfg;
        $this->lng = $lng;
    }

    function showPage(array $tdata) {
        $web['main_head'] = $this->cssLinkFile($this->cfg['theme'], $this->cfg['css']);
        $web['main_footer'] = '';

        /* Add custom css files */
        if (!empty($tdata['web_main']['cssfile']) && is_array($tdata['web_main']['cssfile'])) {
            foreach ($tdata['web_main']['cssfile'] as $cssfile) {
                $web['main_head'] .= $this->cssLinkFile($this->cfg['theme'], $cssfile);
            }
        }
        /* Add custom js files */
        if (!empty($tdata['web_main']['jsfile']) && is_array($tdata['web_main']['jsfile'])) {
            foreach ($tdata['web_main']['jsfile'] as $jsfile) {
                if (file_exists($jsfile)) {
                    $web['main_head'] .= $this->jsLinkFile($jsfile);
                }
            }
        }

        if (!empty($tdata['web_main']['main_head'])) {
            $web['main_head'] .= $tdata['web_main']['main_head'];
        }
        if (!empty($tdata['web_main']['main_head_tpl']) && is_array($tdata['web_main']['main_head_tpl'])) {
            foreach ($tdata['web_main']['main_head_tpl'] as $head_tpl) {
                $web['main_head'] .= $this->getTpl($head_tpl, $tdata);
            }
        }

        if (!empty($tdata['web_main']['main_footer'])) {
            $web['main_footer'] .= $tdata['web_main']['main_footer'];
        }

        /* Load Templates in tdata/tpl */
        if (!empty($tdata['load_tpl']) and is_array($tdata['load_tpl']) && count($tdata['load_tpl']) > 0) {
            foreach ($tdata['load_tpl'] as $tpl) {
                if (!empty($tpl['file']) && !empty($tpl['place'])) {
                    if (empty($tdata[$tpl['place']])) {
                        $tdata[$tpl['place']] = $this->getTpl($tpl['file'], $tdata);
                    } else {
                        $tdata[$tpl['place']] .= $this->getTpl($tpl['file'], $tdata);
                    }
                }
            }
        }

        $web['main_body'] = $this->getTpl($tdata['page'], $tdata);

        echo $this->getTpl('main', array_merge($tdata, $web));
    }

    function getTpl(string $tpl, array $tdata = []) {
        $lng = $this->lng;
        $cfg = $this->cfg;

        ob_start();
        $tpl_file = 'tpl/' . $tdata['theme'] . '/' . $tpl . '.tpl.php';
        !file_exists($tpl_file) ? $tpl_file = 'tpl/default/' . $tpl . '.tpl.php' : null;
        include($tpl_file);

        return ob_get_clean();
    }

    function cssLinkFile(string $theme, string $css) {
        $css_file = 'tpl/' . $theme . '/css/' . $css . '.css';
        !file_exists($css_file) ? $css_file = 'tpl/default/css/default.css' : null;
        $css_file .= '?nocache=' . time(); //TODO: To Remove: avoid cache css while dev
        $css_file = '<link rel="stylesheet" href="' . $css_file . '">' . "\n";

        return $css_file;
    }

    function jsLinkFile(string $jsfile) {
        return '<script src="' . $jsfile . '"></script>' . "\n";
    }

    function msgBox(array $msg) {

        (substr($msg['title'], 0, 2) == 'L_') ? $msg['title'] = $this->lng[$msg['title']] : null;
        (substr($msg['body'], 0, 2) == 'L_') ? $msg['body'] = $this->lng[$msg['body']] : null;
        return $this->getTpl('msgbox', $msg);
    }

    function msgPage(array $msg) {

        $footer = $this->getFooter();
        $menu = $this->getMenu();
        $body = $this->msgBox(['title' => $msg['title'], 'body' => $msg['body']]);
        $tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
        $tdata['css_file'] = $this->getCssFile($this->cfg['theme'], $this->cfg['css']);
        echo $this->getTpl('html_mstruct', $tdata);

        exit();
    }

    function getFooter() {
        /* TODO
          global $db, $cfg;

          $cfg['show_querys'] ?? 0;
          $querys = $db->getQuerys();
          valid_array($querys) ? $num_querys = count($querys) : $num_querys = 0;
          $tdata['num_querys'] = $num_querys;
          $cfg['show_querys'] ? $tdata['querys'] = $querys : null;

          return $this->getTpl('footer', $tdata);
         *
         */
    }

}
