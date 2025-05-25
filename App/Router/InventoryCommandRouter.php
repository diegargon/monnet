<?php
/**
 * Router for handling Inventory related commands.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Router;

use App\Core\AppContext;
use App\Controllers\CmdInventoryController;

class InventoryCommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handle(string $command, array $command_values): array
    {
        $inventoryController = new CmdInventoryController($this->ctx);
        switch ($command) {
            case 'showInventory':
                return $inventoryController->showInventory($command_values);
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido (inventory): ' . $command,
                ];
        }
    }
}
