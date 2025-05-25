<?php
/**
 * Router for handling Ansible task related commands.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Router;

use App\Core\AppContext;
use App\Controllers\CmdTaskAnsibleController;

class TaskAnsibleCommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handle(string $command, array $command_values): array
    {
        $taskAnsibleController = new CmdTaskAnsibleController($this->ctx);
        switch ($command) {
            case 'playbook_exec':
            case 'pbqueue':
                return $taskAnsibleController->execPlaybook($command, $command_values);
            case 'syslog-load':
            case 'journald-load':
                return $taskAnsibleController->getSystemLogs($command, $command_values);
            case 'reboot':
            case 'shutdown':
                return $taskAnsibleController->handleShutdownReboot($command, $command_values);
            case 'create_host_task':
            case 'delete_host_task':
            case 'update_host_task':
            case 'force_exec_task':
                return $taskAnsibleController->mgmtTask($command, $command_values);
            case 'add_ansible_var':
                return $taskAnsibleController->addAnsibleVar($command, $command_values);
            case 'del_ansible_var':
                return $taskAnsibleController->delAnsibleVar($command, $command_values);
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido (task_ansible): ' . $command,
                ];
        }
    }
}
