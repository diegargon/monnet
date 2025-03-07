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
use App\Controllers\CmdHostLogsController;
use App\Controllers\CmdBookmarksController;
use App\Controllers\CmdNetworkController;
use App\Controllers\CmdTaskAnsibleController;
use App\Controllers\UserController;
use App\Controllers\ConfigController;

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
            /*
             * Hosts
             */
            case 'host-details':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getHostDetails($command_values);
                break;
            case 'remove_host':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->removeHost($command, $command_values);
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
                $response = $hostController->submitOwner($command_values);
                break;
            case 'submitHostTimeout':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitHostTimeout($command_values);
                break;
            case 'submitChangeCat':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitChangeHostCategory($command_values);
                break;
            case 'submitManufacture':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitManufacture($command_values);
                break;
            case 'submitMachineType':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitMachineType($command_values);
                break;
            case 'submitSysAval':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitSysAval($command_values);
                break;
            case 'submitOS':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitOS($command_values);
                break;
            case 'submitOSVersion':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitOSVersion($command_values);
                break;
            case 'submitSystemType':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitSystemTime($command_values);
                break;
            case 'submitAccessLink':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitAccessLink($command_values);
                break;
            case 'submitAccessType':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitAccessType($command_values);
                break;
            case 'clearHostAlarms':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->clearHostAlarms($command_values);
                break;
            case 'setHostAlarms':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->setHostAlarms($command_values);
                break;
            case 'toggleMailAlarms':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->toggleMailAlarms($command_values);
                break;
            case 'alarm_ping_disable':
            case 'alarm_port_disable':
            case 'alarm_macchange_disable':
            case 'alarm_newport_disable':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->toggleAlarmType($command, $command_values);
                break;
            case 'alarm_ping_email':
            case 'alarm_port_email':
            case 'alarm_macchange_email':
            case 'alarm_newport_email':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->toggleEmailAlarmType($command, $command_values);
                break;
            case 'updateAlertEmailList':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->setEmailList($command_values);
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
            case 'auto_reload_host_details':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->reloadStatsView($command, $command_values);
                break;
            /*
             *  Hosts Logs
             */
            case 'ack_host_log':
                $hostLogsController = new CmdHostLogsController($this->ctx);
                $response = $hostLogsController->ackHostLog($command_values);
                break;
            case 'logs-reload':
                $hostLogsController = new CmdHostLogsController($this->ctx);
                $response = $hostLogsController->logsReload($command_values);
                break;
            case 'auto_reload_logs':
                $hostLogsController = new CmdHostLogsController($this->ctx);
                $response = $hostLogsController->reloadLogs($command_values);
                break;
            case 'showAlarms':
            case 'showEvents':
                $hostLogsController = new CmdHostLogsController($this->ctx);
                $response = $hostLogsController->getEvents($command);
                break;
            /*
             *  Host Ansible Reports
             */
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
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getAgentsHosts(2);
                break;
            case 'report_alerts':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getAlertHosts(2);
                break;
            case 'report_warns':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->getWarnHosts();
                break;
            case 'clear_alerts':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->clearAlerts();
                break;
            case 'clear_warns':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->clearWarns();
                break;
            case 'submitHost':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitRemoteHost($command_values);
                break;
            case 'submitNewHostCat':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->submitNewHostsCat($command_values);
                break;
            case 'power_on':
                $hostController = new CmdHostController($this->ctx);
                $response = $hostController->powerOn($command_values);
                break;

            /*
             *  Bookmarks
             */
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
            case 'mgmtBookmark':
                $bookmarksController = new CmdBookmarksController($this->ctx);
                $response = $bookmarksController->mgmtBookmark($command, $command_values);
                break;
            case 'submitBookmarkCat':
                $bookmarksController = new CmdBookmarksController($this->ctx);
                $response = $bookmarksController->submitBookmarkCat($command_values);
                break;
            case 'removeBookmarkCat':
                $bookmarksController = new CmdBookmarksController($this->ctx);
                $response = $bookmarksController->removeBookmarkCat($command_values);
                break;
            /*
             * Config
             */
            case 'submitform':
                $cfgController = new ConfigController($this->ctx);
                $response = $cfgController->setMultiple($command_values);
                break;
            /*
             *  User Prefs
             */
            case 'network_select':
            case 'network_unselect':
                $userController = new UserController($this->ctx);
                $response = $userController->setPref($command, $command_values);
                break;

            case 'show_host_cat':
                $userController = new UserController($this->ctx);
                $response = $userController->toggleHostsCat($command, $command_values);
                break;
            case 'show_host_only_cat':
                $userController = new UserController($this->ctx);
                $response = $userController->onlyOneHostsCat($command, $command_values);
                break;

            case 'change_bookmarks_tab':
                $userController = new UserController($this->ctx);
                $response = $userController->changeBookmarksTab($command_values);
                break;
            /*
             *  Network
             */
            case 'mgmtNetworks':
                $networkController = new CmdNetworkController($this->ctx);
                $response = $networkController->manageNetworks($command, $command_values);
                break;
            case 'requestPool':
                $networkController = new CmdNetworkController($this->ctx);
                $response = $networkController->requestPoolIPs($command_values);
                break;
            case 'submitPoolReserver':
                $networkController = new CmdNetworkController($this->ctx);
                $response = $networkController->submitPoolReserver($command_values);
                break;
            /*
             *  Task Ansible
             */
            case 'playbook_exec':
            case 'pbqueue':
                $taskAnsibleController = new CmdTaskAnsibleController($this->ctx);
                $response = $taskAnsibleController->execPlaybook($command, $command_values);
                break;
            case 'syslog-load':
            case 'journald-load':
                $taskAnsibleController = new CmdTaskAnsibleController($this->ctx);
                $response = $taskAnsibleController->getSystemLogs($command, $command_values);
                break;
            /*
             *  Unknown command
             */
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
