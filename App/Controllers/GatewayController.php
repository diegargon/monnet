<?php

/**
 * Router for handling commands in the application.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Core\AppContext;
use App\Services\GatewayService;

class GatewayController
{
    private AppContext $ctx;
    private GatewayService $gatewayService;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->gatewayService = new GatewayService($this->ctx);
    }

    public function handleCommand($command)
    {
        $response = ['status' => 'error', 'error_msg' => 'Unknown Gateway command'];

        if ($command === 'restart-daemon') {
            $send_data = [
                'command' => 'restart-daemon',
                'module' => 'gateway-daemon',
            ];
        } else if ($command === 'reload-pbmeta') {
            $send_data = [
                'command' => 'reload-pbmeta',
                'module' => 'gateway-daemon',
            ];
        } else if ($command === 'reload-config') {
            $send_data = [
                'command' => 'reload-config',
                'module' => 'gateway-daemon',
            ];
        } else {
            return $response;
        }

        $response = $this->gatewayService->sendCommand($send_data);

        return $response;
    }
}

