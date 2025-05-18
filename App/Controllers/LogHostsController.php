<?php

/**
 * LogHostsController
 *
 * Handles operations related to host logs.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Core\AppContext;
use App\Core\DBManager;

use App\Models\LogHostsModel;
use App\Services\LogHostsService;
use App\Services\Filter;
use App\Services\TemplateService;
use App\Helpers\Response;

class LogHostsController
{
    private AppContext $ctx;
    private DBManager $db;
    private LogHostsModel $logHostsModel;
    private LogHostsService $logHostsService;
    private TemplateService $templateService;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = new DBManager($ctx);
        $this->logHostsModel = new LogHostsModel($this->db);
        $this->logHostsService = new LogHostsService($ctx);
        $this->templateService = new TemplateService($ctx);
    }

    /**
     * Reloads logs for a specific host.
     *
     * @param array<string, string|int> $command_values Command values.
     * @return array<string, string|int> Response in JSON format.
     */
    public function logsReload(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $field = 'logs-reload';
        $response = $this->logHostsService->getLogs($target_id, $command_values);

        return Response::stdReturn(true, $response, false, ['command_receive' => $field]);
    }

    /**
     * Acknowledges a host log.
     *
     * @param array<string, string|int> $command_values Command values.
     * @return array<string, string|int> Response in JSON format.
     */
    public function ackHostLog(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
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
     * Retrieves events based on the given command.
     *
     * @param string $command Command name.
     * @return array<string, string|int> Response in JSON format.
     */
    public function getEvents(string $command): array
    {
        $eventsTplData = $this->logHostsService->getEvents($command);

        $reportTpl = $this->templateService->getTpl('events-report', $eventsTplData);

        return Response::stdReturn(true, $reportTpl, false, ['command_receive' => $command]);
    }
}
