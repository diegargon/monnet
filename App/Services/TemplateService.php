<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class TemplateService {
    private $templatesPath = 'tpl/default/';
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx) {
        $this->ctx = $ctx;
        //$this->templatesPath = $templatesPath;
    }

    /**
     * Obtiene una plantilla y la renderiza con los datos proporcionados.
     *
     * @param string $templateName El nombre de la plantilla.
     * @param array $data Los datos para renderizar la plantilla.
     * @return string El HTML renderizado.
     */
    public function getTpl($templateName, $data = []) {
        $templateFile = $this->templatesPath . $templateName . 'tpl.php';

        if (!file_exists($templateFile)) {
            throw new \Exception("Plantilla no encontrada: $templateName");
        }

        // Extraer los datos para que estén disponibles en la plantilla
        extract($data);

        // Capturar la salida del archivo de plantilla
        ob_start();
        include $templateFile;
        return ob_get_clean();
    }
}
