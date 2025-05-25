<?php
/**
 * Router for handling Config related commands.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Router;

use App\Core\AppContext;
use App\Controllers\ConfigController;

class ConfigCommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handle(string $command, array $command_values): array
    {
        $cfgController = new ConfigController($this->ctx);
        switch ($command) {
            case 'submitConfigform':
                return $cfgController->setMultiple($command_values);
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido (config): ' . $command,
                ];
        }
    }
}
