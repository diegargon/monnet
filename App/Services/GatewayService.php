<?php

/**
 * Gateway related services
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 * v1.2
 */

namespace App\Services;

use App\Core\AppContext;
use App\Core\Config;

use App\Services\LogSystemService;
use App\Gateway\GwRequest;

class GatewayService
{
    private AppContext $ctx;
    private string $server_ip;
    private int $server_port;
    private int $socket_timeout = 1;
    private LogSystemService $logSystemService;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $cfg = $ctx->get(Config::class);
        $this->server_ip = (string)$cfg->get('gateway_ip');
        $this->server_port = (int)$cfg->get('gateway_port');
        $this->logSystemService = new LogSystemService($ctx);
    }

    /**
     * Helper method to send a command via the gateway.
     *
     * @param array<string, string|int> $send_data
     * @return array<string, mixed>
     */
    public function sendCommand(array $send_data): array
    {
        try {
            $gwRequest = new GwRequest($this->ctx, $this->server_ip, $this->server_port);
            if ($gwRequest->connect($this->socket_timeout)) {
                $response = $gwRequest->request($send_data);
            } else {
                $this->logSystemService->error('GatewayService: sendCommand: Can not connect');
                return ['status' => 'error', 'error_msg' => 'sendCommand: Can not connect'];
            }
        } catch (\Throwable $e) {
            $context = json_encode([
                'exception' => (string)$e,
                'send_data' => $send_data,
            ]);
            $this->logSystemService->error(
                'GatewayService Exception: ' .
                $e->getMessage() . ' | Context: ' . $context
            );
            return ['status' => 'error', 'error_msg' => 'Exception: ' . $e->getMessage()];
        }

        if (!isset($response['status'])) {
            $context = json_encode([
                'response' => $response,
                'send_data' => $send_data,
            ]);
            $this->logSystemService->error('GatewayService: without status | Context: ' . $context);
            return ['status' => 'error', 'error_msg' => 'Gateway response without status'];
        }

        if ($response['status'] === 'success') {
            if (empty($response['message'])) {
                $context = json_encode([
                    'response' => $response,
                    'send_data' => $send_data,
                ]);
                $this->logSystemService->error('GatewayService: Success but empty response | Ctx: ' . $context);
                return ['status' => 'error', 'error_msg' => 'Status success but empty response'];
            }
            // TODO GW must use response_msg;
            $response['status'] = 'success';
            $response['response_msg'] = $response['message'];
            return $response;
        }

        $error_msg = 'Gateway error response: ';
        if (isset($response['message'])) {
            $error_msg .= $response['message'];
        } else {
            $error_msg .= 'Unknown response from gateway';
        }
        $context = json_encode([
            'response' => $response,
            'send_data' => $send_data,
        ]);
        $this->logSystemService->error('GatewayService: ' . $error_msg . ' | Context: ' . $context);
        return ['status' => 'error', 'error_msg' => $error_msg];
    }
}
