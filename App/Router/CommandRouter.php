<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Router;

use App\Controllers\CmdHostController;
use App\Controllers\CmdBookmarksController;
use App\Controllers\CmdNetworkController;
use App\Controllers\CmdTaskAnsibleController;

class CommandRouter {

    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handleCommand($command, $command_values)
    {
        $response = [];

        switch ($command) :
            // Hosts
            case 'host-details':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getHostDetails($command_values);
                break;
            case 'remove_host':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->removeHost($command_values);
                break;
            case 'toggleDisablePing':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->toggleDisablePing($command_values);
                break;
            case 'setCheckPorts':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->setCheckPorts($command_values);
                break;
            case 'submitHostToken':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitHostToken($command_values);
                break;
            case 'submitHostPort':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->addRemotePort($command_values);
                break;
            case 'deleteHostPort':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->deleteHostPort($command_values);
                break;
            case 'submitCustomServiceName':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitCustomServiceName($command_values);
                break;
            case 'submitTitle':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitTitle($command_values);
                break;
            case 'submitHostname':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitHostname($command_values);
                break;
            case 'submitOwner':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submOwner($command_values);
            case 'submitHostTimeout':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitHostTimeout($command_values);
            case 'submitCat':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitHostCategory($command_values);
            case 'submitManufacture':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitManufacture($command_values);
            case 'submitMachineType':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitMachineType($command_values);
            case 'submitSysAval':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitSysAval($command_values);
            case 'logs-reload':
            case 'auto_reload_logs':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->reloadLogs($command_values);
                break;
            case 'clearHostAlarms':
                break;
            case 'setHostAlarms':
                break;
            case 'toggleMailAlarms':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->handleAlarms($command, $command_values);
                break;
            case 'changeHDTab':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->handleTabChange($command_values);
                break;
            case 'setAlwaysOn':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->setAlwaysOn($command_values);
                break;
            case 'setHighlight':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->setHighlight($command_values);
                break;
            case 'setHostAnsible':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->setHostAnsible($command_values);
                break;
            case 'saveNote':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->saveNote($command_values);
                break;
            // Host Ansible Reports
            case 'report_ansible_hosts':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getAnsibleHosts();
                break;
            case 'report_ansible_hosts_off':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getAnsibleHosts(0);
                break;
            case 'report_ansible_hosts_fail':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getAnsibleHosts(2);
                break;
            case 'report_agents_hosts':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getAgentsHosts(1);
                break;
            case 'report_agents_hosts_off':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getAgentsHosts(0);
                break;
            case 'report_agents_hosts_missing_pings':
                break;
            case 'report_alerts':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getAlertHosts(2);
                break;
            case 'report_warns':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getWarnHosts();
                break;
            case 'ack_host_log':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->ackHostLog($command_values);
                break;
            case 'clear_alerts':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->clearAlerts();
                break;
            case 'clear_warns':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->clearWarns();
                break;

            // Bookmarks
            case 'addBookmark':
                $bookmarksController = new CmdBookmarksController($this->ctx);
                $response = $bookmarksController->addBookmark($command_values);
                break;
            case 'updateBookmark':
                $bookmarksController = new CmdBookmarksController($this->ctx);
                $response = $bookmarksController->updateBookmark($command_values);
                break;
            case 'removeBookmark':
                $bookmarksController = new CmdBookmarksController($this->ctx);
                $response = $bookmarksController->removeBookmark($command_values);
                break;

            // Network
            case 'mgmtNetworks':
                $networkController = new CmdNetworkController($this->ctx);
                $response = $networkController->manageNetworks($command_values);
                break;
            case 'network_select':
            case 'network_unselect':
                $hostController = new CmdNetworkController($this->ctx);
                $response = $hostController->handleNetworkSelection($command_values);
                break;

            // Task Ansible
            case 'playbook_exec':
                $taskAnsibleController = new CmdTaskAnsibleController($this->ctx);
                $response = $taskAnsibleController->executePlaybook($command_values);
                break;
            case 'pbqueue':
                $taskController = new CmdTaskController($this->ctx);
                $response = $taskController->queuePlaybook($command_values);
                break;


            // Comando no reconocido
            default:
                $response = [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido: ' . $command,
                ];
                break;
        endswitch;

        return $response;
    }
}
