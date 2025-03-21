<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Helpers\Response;
use App\Services\AnsibleService;
use App\Services\Filter;
use App\Models\CmdAnsibleModel;
use App\Services\EncryptionService;

class CmdTaskAnsibleController
{
    private \AppContext $ctx;
    private Filter $filter;
    private AnsibleService $ansibleService;

    public function __construct(\AppContext $ctx)
    {
        $this->ansibleService = new AnsibleService($ctx);
        $this->filter = new Filter();
        $this->ctx = $ctx;
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function execPlaybook(string $command, array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $playbook = $this->filter->varString($command_values['value']);
        $extra_vars = [];
        if (!empty($this->filter->varBool($command_values['as_html']))) {
            $as_html = 1;
        } else {
            $as_html = 0;
        }

        if (!empty($command_values['extra_vars'])) {
            $extra_vars = $this->filter->varJson($command_values['extra_vars']);
        }

        if ($command == 'playbook_exec') {
            $response = $this->ansibleService->runPlaybook($target_id, $playbook, $extra_vars);
            if (($response['status'] === 'success') && $as_html) {
                $response = $this->ansibleService->asHtml($response);
            }
        } elseif ($command === 'pbqueue') {
            $response = $this->ansibleService->queueTask($target_id, 1, $playbook, $extra_vars);
        } else {
            return Response::stdReturn(false, 'Unknown Ansible Command');
        }

        if ($response['status'] === "success") {
            $response = $response['response_msg'];
            $extra = ['command_receive' => $command, 'as_html' => $as_html];
            return Response::stdReturn(true, $response, false, $extra);
        } else {
            return Response::stdReturn(false, $response['error_msg']);
        }
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function getSystemLogs(string $command, array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        if (isset($command_values['value'])) {
            $value = $this->filter->varInt($command_values['value']);
        } else {
            $value = 25;
        }

        $lng = $this->ctx->get('lng');
        $hosts = $this->ctx->get('Hosts');
        $host = $hosts->getHostById($target_id);

        if ($command === 'syslog-load') {
            $playbook = 'syslog-linux';
        } else {
            $playbook = 'journald-linux';
        }

        if (valid_array($host) && $host['ansible_enabled']) {
            $extra_vars = [];
            if (is_numeric($value)) {
                $extra_vars['num_lines'] = $value;
            }
            $response = $this->ansibleService->runPlaybook($target_id, $playbook, $extra_vars);
            if ($response['status'] === "success") {
                $debug_lines = $this->ansibleService->fSystemLogs($host, $response);

                return Response::stdReturn(true, $debug_lines, false, ['command_receive' => $command]);
            } else {
                $hosts->setAnsibleAlarm($target_id, $response['error_msg']);
                return Response::stdReturn(false, $response['error_msg']);
            }
        } else {
            return Response::stdReturn(false, $lng['L_ACCESS_METHOD']);
        }
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function handleShutdownReboot(string $command, array $command_values): array
    {
        $hid = $this->filter->varInt($command_values['id']);
        if (!$hid) {
            return Response::stdReturn(false, 'id error');
        }

        $playbook = $command . '-linux';
        $response = $this->ansibleService->runPlaybook($hid, $playbook);
        if ($response['status'] === "success") {
            return Response::stdReturn(true, $response);
        } else {
            return Response::stdReturn(false, $response['error_msg']);
        }
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function mgmtTask(string $command, array $command_values): array
    {
        switch ($command):
            case 'create_host_task':
                $hid = $this->filter->varInt($command_values['hid']);
                break;
            case 'delete_task':
                $hid = $this->filter->varInt($command_values['id']);
                break;
            default:
                $hid = 0;
        endswitch;

        $response_extra = [
            'command' => $command,
        ];

        if ($command === 'delete_task') {
            $cmdAnsibleModel = new CmdAnsibleModel($this->ctx);
            if ($cmdAnsibleModel->deleteTask($hid)) {
                return Response::stdReturn(true, 'Delete Task Success', false, $response_extra);
            } else {
                return Response::stdReturn(false, 'Error deleting task', false, $response_extra);
            }
        }

        switch ($command):
            case 'create_host_task':
                $playbook_id = $this->filter->varInt($command_values['playbook']);
                $next_task_id = $this->filter->varInt($command_values['next_task']);
                $task_trigger = $this->filter->varInt($command_values['task_trigger']);
                $ansible_groups = $this->filter->varInt($command_values['groups']);
                $disable_task = $this->filter->varBool($command_values['disable_task']);
                $task_name = $this->filter->varString($command_values['task_name']);

                if ($task_trigger === 2) {
                    $conditional = $this->filter->varInt($command_values['conditional']);
                    if (empty($conditional)) {
                        $conditional_error = 'Wrong event';
                    } else {
                        $event_id = $conditional;
                    }
                } elseif ($task_trigger === 4) {
                    if (!$this->filter->varCron($command_values['conditional'])) {
                        $conditional_error = 'Wrong Cron, syntax must be a cron expression * * * * *';
                    } else {
                        $crontime = $command_values['conditional'];
                    }
                }

                if (!empty($conditional_error)) {
                    return Response::stdReturn(false, $conditional_error, false, $response_extra);
                }

                $task_data = [
                    'hid' => $hid,
                    'pb_id' => $playbook_id,
                    'trigger_type' => $task_trigger,
                    'task_name' => $task_name,
                    'next_task' => $next_task_id,
                    'disable' => $disable_task,
                ];

                if (isset($event_id)) {
                    $task_data['event_id'] =  $event_id;
                }
                if (isset($crontime)) {
                    $task_data['crontime'] = $crontime;
                }
                if (!isset($ansible_groups)) {
                    $task_data['groups'] =  $ansible_groups;
                }
                $response = $this->ansibleService->createTask($task_data);

                if ($response['status'] === 'success') {
                    return Response::stdReturn(true, $response['response_msg'], false, $response_extra);
                } else {
                    return Response::stdReturn(false, $response['error_msg'], false, $response_extra);
                }
            case 'update_task':
                return Response::stdReturn(false, 'Unknown command', false, $response_extra);
            case 'force_exec_task':
                return Response::stdReturn(false, 'Unknown command', false, $response_extra);
            default:
                return Response::stdReturn(false, 'Unknown command', false, $response_extra);
        endswitch;
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function addAnsibleVar(string $command, array $command_values): array
    {
        $hid = $this->filter->varInt($command_values['host_id']);
        $var_name = $this->filter->varStrict($command_values['var_name']);
        $var_value = $this->filter->varStrict($command_values['var_value']);
        $var_type = $this->filter->varString($command_values['var_type']);

        if ($var_type === "encrypt_value") {
            $vtype = 1;
            $encryptService = new EncryptionService($this->ctx);
            if (!$encryptService) {
                return Response::stdReturn(false, 'Encrypt instance fail. Missing public key?');
            }
            $var_value = $encryptService->encrypt($var_value);
        } else {
            $vtype = 2;
        }

        $cmdAnsibleModel = new CmdAnsibleModel($this->ctx);
        if ($cmdAnsibleModel->addAnsibleVar($hid, $vtype, $var_name, $var_value)) {
            return Response::stdReturn(true, 'Ansible var added', false, ['command' => $command]);
        }

        return Response::stdReturn(false, 'Problem adding the ansible var');

    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function delAnsibleVar(string $command, array $command_values): array
    {
        $cmdAnsibleModel = new CmdAnsibleModel($this->ctx);
        $id = $this->filter->varInt($command_values['id']);
        if ($cmdAnsibleModel->delAnsibleVar($id)) {
            return Response::stdReturn(true, 'Deleted ansible var', false, ['commnand' => $command]);
        }
        return Response::stdReturn(false, 'Error Deleting ansible var', false, ['command' => $command]);
    }
}
