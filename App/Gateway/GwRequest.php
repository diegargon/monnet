<?php

/**
 * Handle Gateway request, abstract Gateway Service from Socket mechanism
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 * v1.0
 */
namespace App\Gateway;

use App\Core\AppContext;
use App\Core\Network\SocketClient;

use RuntimeException;

class GwRequest
{
    private AppContext $ctx;
    private SocketClient $socketClient;
    private string $server_ip;
    private int $server_port;

    public function __construct(AppContext $ctx, string $server_ip, int $server_port)
    {
        $this->ctx = $ctx;
        $this->server_ip = $server_ip;
        $this->server_port = $server_port;

        if (empty($this->server_ip)) {
            throw new RuntimeException('GW: Wrong or empty server IP');
        }

        if ($this->server_port < 1 || $this->server_port > 65535) {
            throw new RuntimeException('GW: Invalid server port');
        }
    }

    /**
     * Connect to the socket server and set timeout.
     *
     * @param int $timeout
     * @return SocketClient
     * @throws RuntimeException
     */
    public function connect(int $timeout = 1): SocketClient
    {
        try {
            $this->socketClient = new SocketClient($this->server_ip, $this->server_port);
            $this->socketClient->setTimeout($timeout);
            return $this->socketClient;
        } catch (\Throwable $e) {
            throw new RuntimeException('GW: Failed to create socket client: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     *
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    public function request(array $request): array
    {
        try {
            # SendAndReceive trigger the connection
            $responseArray = $this->socketClient->sendAndReceive($request);
            if (is_array($responseArray)) {
                return $responseArray;
            }
            return ['status' => 'error', 'error_msg' => 'Unknown error receiving gw response'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'error_msg' => $e->getMessage()];
        }

    }
}
