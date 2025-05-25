<?php
/**
 * Router for handling Log related commands.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Router;

use App\Core\AppContext;
use App\Controllers\LogHostsController;

class LogCommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handle(string $command, array $command_values): array
    {
        $logHostsController = new LogHostsController($this->ctx);
        switch ($command) {
            case 'ack_host_log':
                return $logHostsController->ackHostLog($command_values);
            case 'logs-reload':
            case 'auto_reload_logs':
                return $logHostsController->logsReload($command_values);
            case 'showAlarms':
            case 'showEvents':
                return $logHostsController->getEvents($command);
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido (log): ' . $command,
                ];
        }
    }
}
