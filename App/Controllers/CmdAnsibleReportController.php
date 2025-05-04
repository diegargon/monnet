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
        $target_id = Filter::varInt($command_values['id']);

        $extra = [
            'command_receive' => $command,
            'response_id' => $target_id,
        ];

        if ($this->reportModel->delete($target_id)) {
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
        $report_id = Filter::varInt($command_values['id']);

        $extra = [
            'command_receive' => $command,
            'response_id' => $report_id,
        ];

        if (!isset($this->ansibleService)) {
            $this->ansibleService = new AnsibleService($this->ctx);
        }
        $response = $this->ansibleService->getHtmlReportById($report_id);

        if ($response['status'] === 'success') {
            return Response::stdReturn(true, $response['response_msg'], false, $extra);
        } elseif (!empty($response['error'])) {
            return Response::stdReturn(false, $response['error_msg']);
        }

        return Response::stdReturn(false, 'Error viewReport');
    }
}
