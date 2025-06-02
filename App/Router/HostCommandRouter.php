<?php
/**
 * Router for handling Host related commands.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Router;

use App\Core\AppContext;
use App\Controllers\CmdHostController;

class HostCommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handle(string $command, array $command_values): array
    {
        $hostController = new CmdHostController($this->ctx);
        switch ($command) {
            case 'host-details':
                return $hostController->getHostDetails($command_values);
            case 'remove_host':
                return $hostController->removeHost($command, $command_values);
            case 'toggleDisablePing':
                return $hostController->toggleDisablePing($command_values);
            case 'setCheckPorts':
                return $hostController->setCheckPorts($command_values);
            case 'submitHostToken':
                return $hostController->submitHostToken($command_values);
            case 'submitHostPort':
                return $hostController->addRemotePort($command_values);
            case 'deleteHostPort':
                return $hostController->deleteHostPort($command_values);
            case 'submitCustomServiceName':
                return $hostController->submitCustomServiceName($command_values);
            case 'submitTitle':
                return $hostController->submitTitle($command_values);
            case 'submitHostname':
                return $hostController->submitHostname($command_values);
            case 'submitOwner':
                return $hostController->submitOwner($command_values);
            case 'submitHostTimeout':
                return $hostController->submitHostTimeout($command_values);
            case 'submitChangeCat':
                return $hostController->submitChangeHostCategory($command_values);
            case 'submitManufacture':
                return $hostController->submitManufacture($command_values);
            case 'submitMachineType':
                return $hostController->submitMachineType($command_values);
            case 'submitSysAval':
                return $hostController->submitSysAval($command_values);
            case 'submitLinked':
                return $hostController->submitLinked($command_values);
            case 'submitOS':
                return $hostController->submitOS($command_values);
            case 'submitOSFamily':
                return $hostController->submitOSFamily($command_values);
            case 'submitOSVersion':
                return $hostController->submitOSVersion($command_values);
            case 'submitSystemRol':
                return $hostController->submitSystemRol($command_values);
            case 'submitAccessLink':
                return $hostController->submitAccessLink($command_values);
            case 'submitAccessType':
                return $hostController->submitAccessType($command_values);
            case 'clearHostAlarms':
                return $hostController->clearHostAlarms($command_values);
            case 'setHostAlarms':
                return $hostController->setHostAlarms($command_values);
            case 'toggleMailAlarms':
                return $hostController->toggleMailAlarms($command_values);
            case 'alarm_ping_disable':
            case 'alarm_port_disable':
            case 'alarm_macchange_disable':
            case 'alarm_newport_disable':
            case 'alarm_hostname_disable':
                return $hostController->toggleAlarmType($command, $command_values);
            case 'alarm_ping_email':
            case 'alarm_port_email':
            case 'alarm_macchange_email':
            case 'alarm_newport_email':
                return $hostController->toggleEmailAlarmType($command, $command_values);
            case 'updateAlertEmailList':
                return $hostController->setEmailList($command_values);
            case 'changeHDTab':
                return $hostController->handleTabChange($command_values);
            case 'setAlwaysOn':
                return $hostController->setAlwaysOn($command_values);
            case 'setLinkable':
                return $hostController->setLinkable($command_values);
            case 'setHighlight':
                return $hostController->setHighlight($command_values);
            case 'setHostAnsible':
                return $hostController->setHostAnsible($command_values);
            case 'saveNote':
                return $hostController->saveNote($command_values);
            case 'auto_reload_host_details':
                return $hostController->reloadStatsView($command, $command_values);
            case 'setHostDisable':
                return $hostController->setHostDisable($command_values);
            case 'report_ansible_hosts':
                return $hostController->getAnsibleHosts();
            case 'report_ansible_hosts_off':
                return $hostController->getAnsibleHosts(0);
            case 'report_ansible_hosts_fail':
                return $hostController->getAnsibleHosts(2);
            case 'report_agents_hosts':
                return $hostController->getAgentsHosts(1);
            case 'report_agents_hosts_off':
                return $hostController->getAgentsHosts(0);
            case 'report_agents_hosts_missing_pings':
                return $hostController->getAgentsHosts(2);
            case 'report_alerts':
                return $hostController->getAlertHosts(2);
            case 'report_warns':
                return $hostController->getWarnHosts();
            case 'clear_alerts':
                return $hostController->clearAlerts();
            case 'clear_warns':
                return $hostController->clearWarns();
            case 'submitHost':
                return $hostController->submitHost($command_values);
            case 'submitNewHostsCat':
                return $hostController->submitNewHostsCat($command_values);
            case 'power_on':
                return $hostController->powerOn($command_values);
            case 'submitAgentConfig':
                return $hostController->saveAgentConfig($command_values);
            case 'updateHostConfig':
                return $hostController->updateHostConfig($command_values);
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido (host): ' . $command,
                ];
        }
    }
}
