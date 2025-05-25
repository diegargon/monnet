<?php
/**
 * Router for handling Ansible Report related commands.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Router;

use App\Core\AppContext;
use App\Controllers\CmdAnsibleReportController;

class AnsibleReportCommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handle(string $command, array $command_values): array
    {
        $ansibleReportController = new CmdAnsibleReportController($this->ctx);
        switch ($command) {
            case 'submitDeleteReport':
                return $ansibleReportController->deleteReport($command, $command_values);
            case 'submitViewReport':
                return $ansibleReportController->viewReport($command, $command_values);
            case 'ackReport':
                return $ansibleReportController->ackReport($command, $command_values);
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido (ansible_report): ' . $command,
                ];
        }
    }
}
