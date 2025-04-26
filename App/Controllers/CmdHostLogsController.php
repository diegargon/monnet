<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Models\LogHostsModel;
use App\Services\HostsLogsService;
use App\Services\Filter;
use App\Helpers\Response;
use App\Services\TemplateService;

class CmdHostLogsController
{
    private \AppContext $ctx;
    private LogHostsModel $logHostsModel;
    private HostsLogsService $hostsLogsService;
    private Filter $filter;
    private TemplateService $templateService;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->logHostsModel = new LogHostsModel($ctx);
        $this->hostsLogsService = new HostsLogsService($ctx);
        $this->filter = new Filter();
        $this->templateService = new TemplateService($ctx);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function logsReload(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $field = 'logs-reload';
        $response = $this->hostsLogsService->getLogs($target_id, $command_values);

        return Response::stdReturn(true, $response, false, ['command_receive' => $field]);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function ackHostLog(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varInt($command_values['value']);
        $field = 'ack';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if (($ret = $this->logHostsModel->updateByID($target_id, [$field => $value]))) {
            return Response::stdReturn(true, "$field: successfully $ret");
        }

        return Response::stdReturn(false, "$field: error");
    }

     /**
     *
     * @param string $command
     * @return array<string, string|int>
     */
    public function getEvents(string $command): array
    {
        $eventsTplData = $this->hostsLogsService->getEvents($command);

        $reportTpl = $this->templateService->getTpl('events-report', $eventsTplData);

        return Response::stdReturn(true, $reportTpl, false, ['command_receive' => $command]);
    }
}
