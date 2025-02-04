<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Controllers\HostController;
use App\Controllers\BookmarkController;
use App\Controllers\NetworkController;
use App\Controllers\TaskController;
use App\Controllers\ReportController;

class CommandController {
    public function handleCommand($command, $command_values) {
        $response = [];

        switch ($command) {
            case 'remove_host':
                $hostController = new HostController();
                $response = $hostController->removeHost($command_values);
                break;
            case 'addBookmark':
                $bookmarkController = new BookmarkController();
                $response = $bookmarkController->addBookmark($command_values);
                break;
            case 'mgmtNetworks':
                $networkController = new NetworkController();
                $response = $networkController->manageNetworks($command_values);
                break;
            case 'playbook_exec':
                $taskController = new TaskController();
                $response = $taskController->executePlaybook($command_values);
                break;
            case 'report_ansible_hosts':
                $reportController = new ReportController();
                $response = $reportController->generateAnsibleReport($command_values);
                break;
            default:
                $response = ['error' => 'Comando no reconocido'];
                break;
        }

        return $response;
    }
}
