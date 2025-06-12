<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Core\AppContext;
use App\Core\ConfigService;

class TemplateService
{
    private string $templatesPath = 'tpl/default/';
    private AppContext $ctx;
    private string $theme = 'default';
    private string $theme_css = 'default';

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $ncfg = $this->ctx->get(ConfigService::class);
        $this->theme = $ncfg->get('theme');
        $this->theme_css = $ncfg->get('theme_css');
        //$this->templatesPath = $templatesPath;
    }

    /**
     * Obtiene una plantilla y la renderiza con los datos proporcionados.
     *
     * @param string $templateName El nombre de la plantilla.
     * @param array<string, mixed> $data Los datos para renderizar la plantilla.
     * @return string El HTML renderizado.
     */
    public function getTpl(string $templateName, array $tdata = []): string
    {
        $lng = $this->ctx->get('lng');
        $ncfg = $this->ctx->get(ConfigService::class);
        $user = $this->ctx->get(UserService::class);

        $templateFile = $this->templatesPath . $templateName . '.tpl.php';

        if (!file_exists($templateFile)) {
            throw new \Exception("Plantilla no encontrada: $templateName");
        }

        // Extraer el array
        //extract($tdata);

        // Capturar la salida del archivo de plantilla
        ob_start();
        include $templateFile;
        //TODO if not give problems mixit
        $content = ob_get_clean();
        $content = preg_replace('/>\n\s+/', '>', $content);
        $content = preg_replace('/\s+</', '<', $content);
        $content = preg_replace('/"\s+\/>/', '"/>', $content);
        //Falla en guage.tpl/js al quitar los espacios.
        //$content = preg_replace('/\n\s+/', ' ', $content);
        return $content;
    }

    /**
     * Returns a CSS <link> tag for the given theme and CSS file.
     *
     * @param string $css
     * @return string
     */
    public function cssLink(string $css): string
    {
        if (strpos($css, 'http') === 0) {
            $css_file = $css;
        } else {
            $css_file = 'tpl/' . $this->theme . '/css/' . $css . '.css';
            if (!file_exists($css_file)) {
                $css_file = 'tpl/default/css/default.css';
            }
            $css_file .= '?nocache=' . time();
        }
        return '<link rel="stylesheet" href="' . $css_file . '">' . "\n";
    }

    /**
     * Returns a <script> tag for the given script file.
     *
     * @param string $scriptlink
     * @return string
     */
    public function scriptLink(string $scriptlink): string
    {
        return '<script src="' . $scriptlink . '"></script>' . "\n";
    }

    /**
     * Renders a message box template.
     *
     * @param array<string> $msg
     * @return string|bool
     */
    public function msgBox(array $msg): string|bool
    {
        $lng = $this->ctx->get('lng');
        if (isset($msg['title']) && substr($msg['title'], 0, 2) == 'L_') {
            $msg['title'] = $lng[$msg['title']] ?? $msg['title'];
        }
        if (isset($msg['body']) && substr($msg['body'], 0, 2) == 'L_') {
            $msg['body'] = $lng[$msg['body']] ?? $msg['body'];
        }
        return $this->getTpl('msgbox', $msg);
    }

    /**
     * Renders a full message page and exits.
     *
     * @param array<string> $msg
     * @return void
     */
    public function msgPage(array $msg): void
    {
        $footer = $this->getFooter();
        $menu = '';
        $body = $this->msgBox(['title' => $msg['title'], 'body' => $msg['body']]);
        $tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
        echo $this->getTpl('html_mstruct', $tdata);
        exit();
    }

    /**
     * Returns the footer HTML.
     *
     * @return string
     */
    public function getFooter(): string
    {
        return '';
    }

    /**
     * Renders and outputs a complete page using the provided template data.
     *
     * @param array $tdata Template data for rendering the page.
     * @return void
     */
    public function showPage(array $tdata): void
    {
        $web['main_head'] = $this->cssLink($this->theme_css);
        $web['main_footer'] = '';

        // Add custom css files
        if (!empty($tdata['web_main']['cssfile']) && is_array($tdata['web_main']['cssfile'])) {
            foreach ($tdata['web_main']['cssfile'] as $cssfile) {
                $web['main_head'] .= $this->cssLink($cssfile);
            }
        }
        // Add script link
        if (!empty($tdata['web_main']['scriptlink']) && is_array($tdata['web_main']['scriptlink'])) {
            foreach ($tdata['web_main']['scriptlink'] as $scriptlink) {
                $web['main_head'] .= $this->scriptLink($scriptlink);
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

        // Load Templates in tdata/load_tpl
        if (!empty($tdata['load_tpl']) && is_array($tdata['load_tpl'])) {
            usort($tdata['load_tpl'], function ($a, $b) {
                $weightA = $a['weight'] ?? 5;
                $weightB = $b['weight'] ?? 5;
                return $weightA <=> $weightB;
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
}
