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

        if($command == 'playbook_exec') {
            $response = $this->ansibleService->runPlaybook($target_id, $playbook, $extra_vars);
            if (($response['status'] === 'success') && $as_html) {
                $response = $this->ansibleService->asHtml($response);
            }
        } else if ($command === 'pbqueue') {
//            $response = $this->ansibleService->createTask($target_id, 1, $playbook, $extra_vars);
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
}
