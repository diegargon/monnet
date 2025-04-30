<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
 */

namespace App\Controllers;

use App\Services\Filter;
use App\Helpers\Response;

/* Temp Wrap Pre Migration */

class ConfigController
{
    private \AppContext $ctx;
    private \Config $ncfg;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get('Config');
    }

    /**
     *
     * @param array<string, string|int> $values
     * @return array<string, string|int>
     */
    public function setMultiple(array $values): array
    {
        // TODO 1111: Filter/check values
        $changes = $this->ncfg->setMultiple($values);

        return Response::stdReturn(true, $changes);
    }
}
