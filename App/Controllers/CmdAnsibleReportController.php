<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Services\Filter;
use App\Models\CmdAnsibleReportModel;
use App\Helpers\Response;

class CmdAnsibleReportController {
    private $reportModel;
    private \AppContext $ctx;
    private Filter $filter;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->filter = new Filter();
        $this->reportModel = new CmdAnsibleReportModel($ctx);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function generateAnsibleReport(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $report_type = $this->filter->varString($command_values['type']);

        $report_data = $this->reportModel->getReport($target_id, $report_type);

        if ($report_data) {
            return Response::stdReturn(true, $report_data);
        } else {
            return Response::stdReturn(false, 'Error generating report');
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function deleteReport(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);

        if ($this->reportModel->delete($target_id)) {
            return Response::stdReturn(true, 'Report deleted successfully');
        } else {
            return Response::stdReturn(false, 'Error deleting report');
        }
    }
}
