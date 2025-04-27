<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class TemplateService
{
    private string $templatesPath = 'tpl/default/';
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
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
        $ncfg = $this->ctx->get('Config');

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
        $content = preg_replace('/"\s+\/>/', '"\/>', $content);
        //Falla en guage.tpl/js al quitar los espacios.
        //$content = preg_replace('/\n\s+/', ' ', $content);
        return $content;
    }
}
