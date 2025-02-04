<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Models\cmdAnsibleReportModel;


class CmdAnsibleReportController {
    private $reportModel;
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->reportModel = new cmdAnsibleReportModel($ctx);
    }

    public function generateAnsibleReport($command_values)
    {
        $target_id = $this->filterService->varInt($command_values['id']);
        $report_type = $this->filterService->varString($command_values['type']);

        $report_data = $this->reportModel->getAnsibleReport($target_id, $report_type);

        if ($report_data) {
            return [
                'command_success' => 1,
                'response_msg' => $report_data,
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error generating report',
            ];
        }
    }

    public function deleteReport($command_values)
    {
        $target_id = $this->filterService->varInt($command_values['id']);

        if ($this->reportModel->delete($target_id)) {
            return [
                'command_success' => 1,
                'response_msg' => 'Report deleted successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error deleting report',
            ];
        }
    }
}
