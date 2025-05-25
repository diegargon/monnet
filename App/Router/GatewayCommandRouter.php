<?php
/**
 * Router for handling gateway related commands.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Router;

use App\Core\AppContext;
use App\Controllers\GatewayController;

class GatewayCommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handle(string $command, array $command_values): array
    {
        $gatewayController = new GatewayController($this->ctx);
        switch ($command) {
            case 'restart-daemon':
            case 'reload-pbmeta':
            case 'reload-config':
                return $gatewayController->handleCommand($command);
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido (gateway): ' . $command,
                ];
        }
    }
}
