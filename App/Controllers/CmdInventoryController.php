<?php
/**
 * Controller for Inventory report.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Controllers;

use App\Core\AppContext;
use App\Services\HostService;
use App\Services\NetworksService;
use App\Services\TemplateService;
use App\Helpers\Response;

class CmdInventoryController
{
    private AppContext $ctx;
    private HostService $hostService;
    private NetworksService $networksService;
    private TemplateService $templateService;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->hostService = new HostService($ctx);
        $this->networksService = new NetworksService($ctx);
        $this->templateService = new TemplateService($ctx);
    }

    /**
     * Muestra el informe de inventario.
     * @param array $command_values
     * @return array
     */
    public function showInventory(array $command_values): array
    {
        $hosts = $this->hostService->getAll();
        $networks = $this->networksService->getNetworks();

        $tdata = [
            'hosts' => $hosts,
            'networks' => $networks
        ];

        $report = $this->templateService->getTpl('inventory-report', $tdata);

        return Response::stdReturn(true, 'ok', false, [
            'command_receive' => 'showInventory',
            'response_msg' => $report,
        ]);
    }
}
