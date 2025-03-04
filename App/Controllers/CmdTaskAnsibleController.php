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

class CmdTaskAnsibleController
{
    private \AppContext $ctx;
    private Filter $filter;
    private \App\Services\AnsibleService $ansibleService;

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
        $extra_vars = $this->filter->varJson($command_values['extra_vars']);

        if($command == 'playbook_exec') {
            $response = $this->ansibleService->runPlaybook($target_id, $playbook, $extra_vars);
        } else if ($command === 'pbqueue') {
//            $response = $this->ansibleService->createTask($target_id, 1, $playbook, $extra_vars);
        }

        if ($response['status'] === "success") {
            return Response::stdReturn(true, $response);
        } else {
            return Response::stdReturn(false, $response['error_msg']);
        }
    }
}
