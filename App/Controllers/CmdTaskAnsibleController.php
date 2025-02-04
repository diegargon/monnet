<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

class CmdTaskAnsibleController
{
    private \AppContext $ctx;
    private $ansibleService;

    public function __construct(\AppContext $ctx)
    {
        $this->ansibleService = new AnsibleService($ctx);
        $this->ctx = $ctx;
    }

    public function executePlaybook($command_values)
    {
        $target_id = $this->taskModel->varInt($command_values['id']);
        $playbook = $this->taskModel->varString($command_values['value']);
        $extra_vars = $this->taskModel->varJson($command_values['extra_vars']);

        $response = $this->ansibleService->runPlaybook($target_id, $playbook, $extra_vars);

        if ($response['status'] === "success") {
            return [
                'command_success' => 1,
                'response_msg' => $response,
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => $response['error_msg'],
            ];
        }
    }
}
