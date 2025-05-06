<?php

/**
 *
 * @author diego/@/envigo.net
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
    private AnsibleService $ansibleService;

    public function __construct(\AppContext $ctx)
    {
        $this->ansibleService = new AnsibleService($ctx);
        $this->ctx = $ctx;
    }

    /**
     *
     * @param string $command
     * @param array<string, mixed> $command_values
     * @return array<string, string|int>
     */
    public function execPlaybook(string $command, array $command_values): array
    {
        $hid = Filter::varInt($command_values['id']);
        $playbook = Filter::varString($command_values['value']);
        $extra_vars = [];
        $as_html = !empty(Filter::varBool($command_values['as_html'])) ? 1 : 0;

        if (empty($playbook)) {
            return Response::stdReturn(false, 'Playbook its mandatory');
        }
        if (!empty($command_values['extra_vars'])) {
            $extra_vars = Filter::varJson($command_values['extra_vars']);
        }

        if ($command == 'playbook_exec') {
            $response = $this->ansibleService->runPlaybook($hid, $playbook, $extra_vars);
            if (($response['status'] === 'success') && $as_html) {
                $response = $this->ansibleService->asHtml($response['response_msg']);
            }
        } elseif ($command === 'pbqueue') {
            $response = $this->ansibleService->queueTask($hid, 1, $playbook, $extra_vars);
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
     * @param array<string, mixed> $command_values
     * @return array<string, string|int>
     */
    public function getSystemLogs(string $command, array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = isset($command_values['value']) ? Filter::varInt($command_values['value']) : 25;

        $lng = $this->ctx->get('lng');
        $hosts = $this->ctx->get('Hosts');
        $host = $hosts->getHostById($target_id);

        $playbook = $command === 'syslog-load' ? 'std-syslog-linux' : 'std-journald-linux';

        if (valid_array($host) && $host['ansible_enabled']) {
            $extra_vars = [];
            if (is_numeric($value)) {
                $extra_vars['num_lines'] = $value;
            }
            $response = $this->ansibleService->runPlaybook($target_id, $playbook, $extra_vars);
            // Connection Error Check
            if ($response['status'] !== 'success') {
                $hosts->setAnsibleAlarm($target_id, $response['error_msg']);
                return Response::stdReturn(false, $response['error_msg'], false, ['command_receive' => $command]);
            }
            // Ansible Error return check
            if (!isset($response['response_msg']['result'])) {
                return Response::stdReturn(false, 'Response format error', false, ['command_receive' => $command]);
            }
            $result = $response['response_msg']['result'];
            if (isset($result['status']) && $result['status'] === 'error') {
                return Response::stdReturn(false, $result['message'], false, ['command_receive' => $command]);
            }
            $debug_lines = $this->ansibleService->fSystemLogs($host, $result);

            return Response::stdReturn(true, $debug_lines, false, ['command_receive' => $command]);

        } else {
            return Response::stdReturn(false, $lng['L_ACCESS_METHOD']);
        }
    }

    /**
     *
     * @param string $command
     * @param array<string, mixed> $command_values
     * @return array<string, string|int>
     */
    public function handleShutdownReboot(string $command, array $command_values): array
    {
        $hid = Filter::varInt($command_values['id']);
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
     * @param array<string, mixed> $command_values
     * @return array<string, string|int>
     */
    public function mgmtTask(string $command, array $command_values): array
    {
        $response_add = [
            'command' => $command,
        ];
        switch ($command) :
            case 'create_host_task':
                $hid = Filter::varInt($command_values['hid']);
                $response_add['hid'] = $hid;
                break;
            case 'update_host_task':
            case 'delete_host_task':
                $tid = Filter::varInt($command_values['id']);
                $response_add['tid'] = $tid;
                break;
            default:
                $hid = 0;
        endswitch;

        switch ($command) :
            case 'delete_host_task':
                $cmdAnsibleModel = new CmdAnsibleModel($this->ctx);
                if ($cmdAnsibleModel->deleteTask($tid)) {
                    return Response::stdReturn(true, 'Delete Task Success', false, $response_add);
                } else {
                    return Response::stdReturn(false, 'Error deleting task', false, $response_add);
                }
                break;
            case 'create_host_task':
                $task_data = $this->checkTaskFields($hid, $command, $command_values);
                if (isset($task_data['status']) && $task_data['status'] == 'error') {
                    return Response::stdReturn(false, $task_data['error_msg'], false, $response_add);
                }
                $response = $this->ansibleService->createTask($task_data);

                if ($response['status'] === 'success') {
                    return Response::stdReturn(true, $response['response_msg'], false, $response_add);
                } else {
                    return Response::stdReturn(false, $response['error_msg'], false, $response_add);
                }
                break;
            case 'update_host_task':
                $task_data = $this->checkTaskFields($tid, $command, $command_values);
                if (isset($task_data['status']) && $task_data['status'] == 'error') {
                    return Response::stdReturn(false, $task_data['error_msg'], false, $response_add);
                }
                $response = $this->ansibleService->updateTask($tid, $task_data);

                if ($response['status'] === 'success') {
                    return Response::stdReturn(true, $response['response_msg'], false, $response_add);
                } else {
                    return Response::stdReturn(false, $response['error_msg'], false, $response_add);
                }
                break;
            case 'force_exec_task':
            default:
                return Response::stdReturn(false, 'Unknown command', false, $response_add);
        endswitch;
    }

    /**
     *
     * @param string $command
     * @param array<string, mxied> $command_values
     * @return array<string, string|int>
     */
    public function addAnsibleVar(string $command, array $command_values): array
    {
        $hid = Filter::varInt($command_values['host_id']);
        $var_name = Filter::varStrict($command_values['var_name']);
        $var_value = Filter::varStrict($command_values['var_value']);
        $var_type = Filter::varString($command_values['var_type']);

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
     * @param array<string, mixed> $command_values
     * @return array<string, string|int>
     */
    public function delAnsibleVar(string $command, array $command_values): array
    {
        $cmdAnsibleModel = new CmdAnsibleModel($this->ctx);
        $id = Filter::varInt($command_values['id']);
        if ($cmdAnsibleModel->delAnsibleVar($id)) {
            return Response::stdReturn(true, 'Deleted ansible var', false, ['commnand' => $command]);
        }
        return Response::stdReturn(false, 'Error Deleting ansible var', false, ['command' => $command]);
    }

    /**
     *
     * @param int $id
     * @param string $command
     * @param array<string, int|string> $command_values
     * @return array
     */
    private function checkTaskFields(int $id, string $command, array $command_values)
    {
        //$playbook_id = Filter::varString($command_values['playbook']);
        $playbook_id = $command_values['playbook'];
        $next_task_id = Filter::varInt($command_values['next_task']);
        $task_trigger = Filter::varInt($command_values['task_trigger']);
        $ansible_groups = Filter::varInt($command_values['groups']);
        $disable_task = Filter::varBool($command_values['disable_task']);
        $task_name = Filter::varString($command_values['task_name']);

        if (empty($playbook_id)) {
            return ['status' => 'error', 'error_msg' => 'Playbook id: ', $playbook_id];
        }

        if (empty($task_name)) {
            return ['status' => 'error', 'error_msg' => 'Task name: ', $task_name];
        }

        if (empty($task_trigger)) {
            return ['status' => 'error', 'error_msg' => 'Task trigger can not be empty'];
        }
        empty($disable_task) ? $disable_task = 0 : null;


        if ($task_trigger === 3) {
            $conditional = Filter::varInt($command_values['conditional']);
            if (empty($conditional)) {
                $conditional_error = 'Wrong event';
            } else {
                $event_id = $conditional;
            }
        } elseif ($task_trigger === 4) {
            if (!Filter::varCron($command_values['conditional'])) {
                $conditional_error = 'Wrong Cron, syntax must be a cron expression * * * * *';
            } else {
                $crontime = $command_values['conditional'];
            }
        } elseif ($task_trigger === 5) {
            $interval_seconds = Filter::varInterval($command_values['conditional']);
            if (!$interval_seconds) {
                $conditional_error = 'Wrong interval 5m 5h 1d 1w 1mo 1y';
            } else {
                $task_interval = $command_values['conditional'];
            }
        }

        if (!empty($conditional_error)) {
            return ['status' => 'error', 'error_msg' => $conditional_error];
        }

        $task_data = [
            'pid' => $playbook_id,
            'trigger_type' => $task_trigger,
            'task_name' => $task_name,
            'next_task' => $next_task_id,
            'disable' => $disable_task,
        ];
        if ($command == 'create_host_task') {
            $task_data['hid'] = $id;
        }
        if (isset($event_id)) {
            $task_data['event_id'] =  $event_id;
        }
        if (isset($crontime)) {
            $task_data['crontime'] = $crontime;
        }
        if (!empty($interval_seconds) && is_numeric($interval_seconds)) {
            $task_data['interval_seconds'] = $interval_seconds;
        }
        if (!empty($task_interval)) {
            $task_data['task_interval'] = $task_interval;
        }
        /*
        if (!isset($ansible_groups)) {
            $task_data['groups'] =  $ansible_groups;
        }
        */

        return $task_data;
    }
}
