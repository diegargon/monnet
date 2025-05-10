<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Services\AnsibleService;
use App\Services\Filter;
use App\Models\CmdAnsibleReportModel;
use App\Helpers\Response;

class CmdAnsibleReportController
{
    private CmdAnsibleReportModel $reportModel;
    private AnsibleService $ansibleService;
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->reportModel = new CmdAnsibleReportModel($ctx);
    }

    /**
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function deleteReport(string $command, array $command_values): array
    {
        $rid = Filter::varInt($command_values['id']);

        $extra = [
            'command_receive' => $command,
            'response_id' => $rid,
        ];

        if ($this->reportModel->delete($rid)) {
            return Response::stdReturn(true, 'Report deleted successfully', false, $extra);
        } else {
            return Response::stdReturn(false, 'Error deleting report');
        }
    }

    /**
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function viewReport(string $command, array $command_values): array
    {
        $rid = Filter::varInt($command_values['id']);

        $extra = [
            'command_receive' => $command,
            'response_id' => $rid,
        ];

        if (!isset($this->ansibleService)) {
            $this->ansibleService = new AnsibleService($this->ctx);
        }
        $response = $this->ansibleService->getHtmlReportById($rid);

        if ($response['status'] === 'success') {
            return Response::stdReturn(true, $response['response_msg'], false, $extra);
        } elseif (!empty($response['error'])) {
            return Response::stdReturn(false, $response['error_msg']);
        }

        return Response::stdReturn(false, 'Error viewReport');
    }

    public function ackReport(string $command, array $command_values): array
    {
        $rid = Filter::varInt($command_values['id']);
        $value = Filter::varBool($command_values['value']);

        $this->reportModel->setAck($rid, $value);

        return Response::stdReturn(true, 'Ack Report');
    }
}
