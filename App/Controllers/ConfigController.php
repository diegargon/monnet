<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
 */

namespace App\Controllers;

use App\Core\AppContext;
use App\Core\ConfigService;

use App\Services\Filter;
use App\Services\GatewayService;
use App\Helpers\Response;


class ConfigController
{
    private AppContext $ctx;
    private ConfigService $ncfg;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get(ConfigService::class);
    }

    /**
     *
     * @param array<string, string|int> $values
     * @return array<string, string|int>
     */
    public function setMultiple(array $values): array
    {
        // TODO 1111: Filter/check values
        $num_changes = $this->ncfg->setMultiple($values);

        if ($num_changes > 0) {
            $gatewayService = new GatewayService(($this->ctx));
            $gatewayService->reloadConfig();
        }
        return Response::stdReturn(true, $num_changes);
    }
}
