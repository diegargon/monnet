<?php

/**
 * Gateway related services
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Core\AppContext;

use App\Services\LogSystemService;
use App\Gateway\GwRequest;

class GatewayService
{
    /** @var AppContext */
    private AppContext $ctx;

    /** @var int */
    private int $socket_timeout = 1;

    /** @var LogSystemService */
    private LogSystemService $logSystemService;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->logSystemService = new LogSystemService($ctx);
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
     *
     * @return array<string, mixed>
     */
    public function pingGateway(): array
    {
        $data = ['timestamp' => microtime(true)];
        $send_data = [
            'command' => 'ping',
            'module' => 'gateway-daemon',
            'data' => $data,
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
        try {
            $gwRequest = new GwRequest($this->ctx);
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
            $response['response_msg'] = $response['message'];
            return $response;
        }

        $error_msg = 'Gateway erro response: ';
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
