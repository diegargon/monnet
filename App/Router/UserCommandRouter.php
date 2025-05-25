<?php
/**
 * Router for handling User related commands.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Router;

use App\Core\AppContext;
use App\Controllers\UserController;

class UserCommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handle(string $command, array $command_values): array
    {
        $userController = new UserController($this->ctx);
        switch ($command) {
            case 'network_select':
            case 'network_unselect':
            case 'footer_dropdown_status':
                return $userController->setPref($command, $command_values);
            case 'show_host_cat':
                return $userController->toggleHostsCat($command, $command_values);
            case 'show_host_only_cat':
                return $userController->onlyOneHostsCat($command, $command_values);
            case 'updateProfile':
                return $userController->updateProfile($command_values);
            case 'createUser':
                return $userController->createUser($command_values);
            case 'change_bookmarks_tab':
                return $userController->changeBookmarksTab($command_values);
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido (user): ' . $command,
                ];
        }
    }
}
