<?php
/**
 * Router for handling Netwwork related commands.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Router;

use App\Core\AppContext;
use App\Controllers\CmdNetworkController;

class NetworkCommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handle(string $command, array $command_values): array
    {
        $networkController = new CmdNetworkController($this->ctx);
        switch ($command) {
            case 'mgmtNetworks':
                return $networkController->manageNetworks($command, $command_values);
            case 'requestPool':
                return $networkController->requestPoolIPs($command_values);
            case 'submitPoolReserver':
                return $networkController->submitPoolReserver($command_values);
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido (network): ' . $command,
                ];
        }
    }
}
