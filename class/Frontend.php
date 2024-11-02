<?php

!defined('IN_WEB') ? exit : true;

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 *
 */
class Frontend
{
    /**
     * @var array<string|int> $cfg
     */
    private array $cfg;

    /**
     * @var array<string> $lang
     */
    private array $lng;

    public function __construct(array &$cfg, array $lng)
    {
        $this->cfg = &$cfg; //& due be order config.priv items in some pages, rethink that
        $this->lng = $lng;
    }

    /**
     * @param array<mixed> $tdata
     */
    public function showPage(array $tdata): void
    {
        $web['main_head'] = $this->cssLinkFile($this->cfg['theme'], $this->cfg['css']);
        $web['main_footer'] = '';

        /* Add custom css files */
        if (!empty($tdata['web_main']['cssfile']) && is_array($tdata['web_main']['cssfile'])) {
            foreach ($tdata['web_main']['cssfile'] as $cssfile) {
                $web['main_head'] .= $this->cssLinkFile($this->cfg['theme'], $cssfile);
            }
        }
        /* Add script link */
        if (!empty($tdata['web_main']['scriptlink']) && is_array($tdata['web_main']['scriptlink'])) {
            foreach ($tdata['web_main']['scriptlink'] as $scriptlink) {
                if (
                    (strpos($scriptlink, 'http') === 0) ||
                    (file_exists($scriptlink))
                ) {
                    $web['main_head'] .= $this->scriptLink($scriptlink);
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

        if (!empty($tdata['web_main']['main_footer_tpl']) && is_array($tdata['web_main']['main_footer_tpl'])) {
            foreach ($tdata['web_main']['main_footer_tpl'] as $footer_tpl) {
                $web['main_footer'] .= $this->getTpl($footer_tpl, $tdata);
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

    /**
     * @var array<mixed> $tdata
     */
    public function getTpl(string $tpl, array $tdata = []): string|bool
    {
        $lng = $this->lng;
        $cfg = $this->cfg;

        ob_start();
        $tpl_file = 'tpl/' . $cfg['theme'] . '/' . $tpl . '.tpl.php';
        !file_exists($tpl_file) ? $tpl_file = 'tpl/default/' . $tpl . '.tpl.php' : null;
        include($tpl_file);

        return ob_get_clean();
    }

    public function cssLinkFile(string $theme, string $css): string
    {
        $css_file = 'tpl/' . $theme . '/css/' . $css . '.css';
        !file_exists($css_file) ? $css_file = 'tpl/default/css/default.css' : null;
        $css_file .= '?nocache=' . time(); //TODO: To Remove: avoid cache css while dev
        $css_file = '<link rel="stylesheet" href="' . $css_file . '">' . "\n";

        return $css_file;
    }

    public function scriptLink(string $scriptlink): string
    {
        //TODO SEC
        return '<script src="' . $scriptlink . '"></script>' . "\n";
    }

    /**
     * @var array<string> $msg
     */
    public function msgBox(array $msg): string
    {

        (substr($msg['title'], 0, 2) == 'L_') ? $msg['title'] = $this->lng[$msg['title']] : null;
        (substr($msg['body'], 0, 2) == 'L_') ? $msg['body'] = $this->lng[$msg['body']] : null;

        return $this->getTpl('msgbox', $msg);
    }

    /**
     * @var array<string> $msg
     */
    public function msgPage(array $msg): void
    {

        $footer = $this->getFooter();
        //$menu = $this->getMenu();
        $menu = '';
        $body = $this->msgBox(['title' => $msg['title'], 'body' => $msg['body']]);
        $tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
        //$tdata['css_file'] = $this->getCssFile($this->cfg['theme'], $this->cfg['css']);
        echo $this->getTpl('html_mstruct', $tdata);

        exit();
    }

    public function getFooter()
    {
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
