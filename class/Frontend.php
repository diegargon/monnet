<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
 */
!defined('IN_WEB') ? exit : true;

use App\Core\AppContext;

class Frontend
{
    /** @var AppContext $ctx */
    private AppContext $ctx;

    /**  @var array<string,string> $lng */
    private array $lng;

    /**
     *
     * @var \Config
     */
    private \Config $ncfg;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->lng = &$this->ctx->get('lng');
        $this->ncfg = $this->ctx->get('Config');
    }

    /**
     *
     * @param array<mixed> $tdata
     * @return void
     */
    public function showPage(array $tdata): void
    {
        $web['main_head'] = $this->cssLinkFile($this->ncfg->get('theme'), $this->ncfg->get('theme_css'));
        $web['main_footer'] = '';

        /* Add custom css files */
        if (!empty($tdata['web_main']['cssfile']) && is_array($tdata['web_main']['cssfile'])) {
            foreach ($tdata['web_main']['cssfile'] as $cssfile) {
                $web['main_head'] .= $this->cssLinkFile($this->ncfg->get('theme'), $cssfile);
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
        if (!empty($tdata['load_tpl']) && is_array($tdata['load_tpl'])) {
            usort($tdata['load_tpl'], function ($a, $b) {
                $weightA = $a['weight'] ?? 5;
                $weightB = $b['weight'] ?? 5;
                return $weightA <=> $weightB; // Ascendent
            });
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
     *
     * @param array<mixed> $tdata
     * @return string|bool
     */
    public function getTpl(string $tpl, array $tdata = []): string|bool
    {
        $lng = $this->lng;
        $ncfg = $this->ctx->get('Config');
        $user = $this->ctx->get('User');

        ob_start();
        $tpl_file = 'tpl/' . $ncfg->get('theme') . '/' . $tpl . '.tpl.php';
        !file_exists($tpl_file) ? $tpl_file = 'tpl/default/' . $tpl . '.tpl.php' : null;
        include($tpl_file);

        return ob_get_clean();
    }

    /**
     *
     * @param string $theme
     * @param string $css
     * @return string
     */
    public function cssLinkFile(string $theme, string $css): string
    {
        $css_file = 'tpl/' . $theme . '/css/' . $css . '.css';
        !file_exists($css_file) ? $css_file = 'tpl/default/css/default.css' : null;
        $css_file .= '?nocache=' . time(); //TODO: To Remove: avoid cache css while dev
        $css_file = '<link rel="stylesheet" href="' . $css_file . '">' . "\n";

        return $css_file;
    }

    /**
     *
     * @param string $scriptlink
     *
     * @return string
     */
    public function scriptLink(string $scriptlink): string
    {
        //TODO SEC
        return '<script src="' . $scriptlink . '"></script>' . "\n";
    }

    /**
     *
     * @param array<string> $msg
     * @return string|bool
     */
    public function msgBox(array $msg): string|bool
    {

        (substr($msg['title'], 0, 2) == 'L_') ? $msg['title'] = $this->lng[$msg['title']] : null;
        (substr($msg['body'], 0, 2) == 'L_') ? $msg['body'] = $this->lng[$msg['body']] : null;

        return $this->getTpl('msgbox', $msg);
    }

    /**
     *
     * @param array<string> $msg
     * @return void
     */
    public function msgPage(array $msg): void
    {

        $footer = $this->getFooter();
        //$menu = $this->getMenu();
        $menu = '';
        $body = $this->msgBox(['title' => $msg['title'], 'body' => $msg['body']]);
        $tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
        //$tdata['css_file'] = $this->getCssFile($this->ncfg->get('theme'), $this->ncfg->get('theme_css'));
        echo $this->getTpl('html_mstruct', $tdata);

        exit();
    }

    /** @return string */
    public function getFooter(): string
    {
        return '';
    }
}
