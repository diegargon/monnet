<?php

/**
 * Main Router for handling commands in the application.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Router;

use App\Core\AppContext;
use App\Router\HostCommandRouter;
use App\Router\LogCommandRouter;
use App\Router\BookmarkCommandRouter;
use App\Router\ConfigCommandRouter;
use App\Router\UserCommandRouter;
use App\Router\NetworkCommandRouter;
use App\Router\AnsibleReportCommandRouter;
use App\Router\TaskAnsibleCommandRouter;
use App\Router\GatewayCommandRouter;

class CommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    /**
     * Mapea un comando a su grupo correspondiente.
     *
     * @param string $command
     * @return string|null
     */
    private function getCommandGroup(string $command): ?string
    {
        $groups = [
            'host' => [
                'host-details', 'remove_host', 'toggleDisablePing', 'setCheckPorts', 'submitHostToken',
                'submitHostPort', 'deleteHostPort', 'submitCustomServiceName', 'submitTitle', 'submitHostname',
                'submitOwner', 'submitHostTimeout', 'submitChangeCat', 'submitManufacture', 'submitMachineType',
                'submitSysAval', 'submitLinked', 'submitOS', 'submitOSFamily', 'submitOSVersion', 'submitSystemRol',
                'submitAccessLink', 'submitAccessType', 'clearHostAlarms', 'setHostAlarms', 'toggleMailAlarms',
                'alarm_ping_disable', 'alarm_port_disable', 'alarm_macchange_disable', 'alarm_newport_disable',
                'alarm_hostname_disable', 'alarm_ping_email', 'alarm_port_email', 'alarm_macchange_email',
                'alarm_newport_email', 'updateAlertEmailList', 'changeHDTab', 'setAlwaysOn', 'setLinkable',
                'setHighlight', 'setHostAnsible', 'saveNote', 'auto_reload_host_details', 'setHostDisable',
                'report_ansible_hosts', 'report_ansible_hosts_off', 'report_ansible_hosts_fail', 'report_agents_hosts',
                'report_agents_hosts_off', 'report_agents_hosts_missing_pings', 'report_alerts', 'report_warns',
                'clear_alerts', 'clear_warns', 'submitHost', 'submitNewHostsCat', 'power_on', 'submitAgentConfig'
            ],
            'log' => [
                'ack_host_log', 'logs-reload', 'auto_reload_logs', 'showAlarms', 'showEvents', 'submitBitacora'
            ],
            'bookmark' => [
                'addBookmark', 'updateBookmark', 'removeBookmark', 'mgmtBookmark', 'submitBookmarkCat', 'removeBookmarkCat'
            ],
            'config' => [
                'submitConfigform'
            ],
            'user' => [
                'network_select', 'network_unselect', 'footer_dropdown_status', 'show_host_cat', 'show_host_only_cat',
                'updateProfile', 'createUser', 'change_bookmarks_tab'
            ],
            'network' => [
                'mgmtNetworks', 'requestPool', 'submitPoolReserver'
            ],
            'ansible_report' => [
                'submitDeleteReport', 'submitViewReport', 'ackReport'
            ],
            'task_ansible' => [
                'playbook_exec', 'pbqueue', 'syslog-load', 'journald-load', 'reboot', 'shutdown',
                'create_host_task', 'delete_host_task', 'update_host_task', 'force_exec_task',
                'add_ansible_var', 'del_ansible_var'
            ],
            'gateway' => [
                'restart-daemon', 'reload-pbmeta', 'reload-config'
            ]
        ];
        foreach ($groups as $group => $commands) {
            if (in_array($command, $commands, true)) {
                return $group;
            }
        }
        return null;
    }

    /**
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, mixed>
     */
    public function handleCommand(string $command, array $command_values): array
    {
        $group = $this->getCommandGroup($command);
        $response = [];

        switch ($group) {
            case 'host':
                $router = new HostCommandRouter($this->ctx);
                $response = $router->handle($command, $command_values);
                break;
            case 'log':
                $router = new LogCommandRouter($this->ctx);
                $response = $router->handle($command, $command_values);
                break;
            case 'bookmark':
                $router = new BookmarkCommandRouter($this->ctx);
                $response = $router->handle($command, $command_values);
                break;
            case 'config':
                $router = new ConfigCommandRouter($this->ctx);
                $response = $router->handle($command, $command_values);
                break;
            case 'user':
                $router = new UserCommandRouter($this->ctx);
                $response = $router->handle($command, $command_values);
                break;
            case 'network':
                $router = new NetworkCommandRouter($this->ctx);
                $response = $router->handle($command, $command_values);
                break;
            case 'ansible_report':
                $router = new AnsibleReportCommandRouter($this->ctx);
                $response = $router->handle($command, $command_values);
                break;
            case 'task_ansible':
                $router = new TaskAnsibleCommandRouter($this->ctx);
                $response = $router->handle($command, $command_values);
                break;
            case 'gateway':
                $router = new GatewayCommandRouter($this->ctx);
                $response = $router->handle($command, $command_values);
                break;
            default:
                $response = [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido: ' . $command,
                ];
                break;
        }
        $response['command_receive'] = $command;
        return $response;
    }
}
