<?php

/**
 * Gateway related services
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Gateway\GwRequest;

class GatewayService
{
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    /**
     * Sends the restart-daemon command.
     *
     * @return array<string, string|int>
     */
    public function restartDaemon(): array
    {
        $send_data = [
            'command' => 'restart-daemon',
            'module' => 'gateway-daemon',
        ];

        return $this->sendCommand($send_data);
    }

    /**
     * Sends the reload-pbmeta command.
     *
     * @return array<string, string|int>
     */
    public function reloadPbMeta(): array
    {
        $send_data = [
            'command' => 'reload-pbmeta',
            'module' => 'gateway-daemon',
        ];

        return $this->sendCommand($send_data);
    }

    /**
     * Sends the reload-config command.
     *
     * @return array<string, string|int>
     */
    public function reloadConfig(): array
    {
        $send_data = [
            'command' => 'reload-config',
            'module' => 'gateway-daemon',
        ];


        return $this->sendCommand($send_data);
    }

    /**
     * Helper method to send a command via the gateway.
     *
     * @param array<string, string|int> $send_data
     * @return array<string, string|int>
     */
    public function sendCommand(array $send_data): array
    {
        $gwRequest = new GwRequest($this->ctx);
        $response = $gwRequest->request($send_data);

        if (!isset($response['status'])) {
            return ['status' => 'error', 'error_msg' => 'Gateway response without status'];
        }

        if ($response['status'] === 'success') {
            if (empty($response['message'])) {
                return ['status' => 'error', 'error_msg' => 'Status success but empty response'];
            }
            return ['status' => 'success', 'response_msg' => $response['message']];
        }

        $error_msg = 'Gateway command error: ';
        if (isset($response['message'])) {
            $error_msg .= $response['error_msg'];
        } else {
            $error_msg .= 'Unknown response from gateway';
        }

        return ['status' => 'error', 'error_msg' => $error_msg];
    }
}
