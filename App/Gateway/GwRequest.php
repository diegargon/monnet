<?php

/**
 * Handle Gateway request, abstract Gateway Service from Socket mechanism
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

# TODO must throw exceptions, GatewayService must catch that exceptions and return the status.

namespace App\Gateway;

use App\Core\Network\SocketClient;
use RuntimeException;

class GwRequest
{
    /**
     * @var \AppContext
     */
    private \AppContext $ctx;

    /**
     * @var SocketClient
     */
    private SocketClient $socketClient;

    /**
     * @var string
     */
    private string $server_ip;

    /**
     * @var int
     */
    private int $server_port;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $ncfg = $ctx->get('Config');

        $this->server_ip = (string)$ncfg->get('ansible_server_ip');
        $this->server_port = (int)$ncfg->get('ansible_server_port');

        if(empty($this->server_ip)) {
            return ['status' => 'error', 'GW: Wrong or empty server IP'];
        }

        if ($this->server_port < 1 || $this->server_port > 65535) {
            throw new RuntimeException('GW: Invalid server port');
        }
    }

    /**
     *
     * @return SocketClient
     */
    public function connect(): SocketClient
    {
        $this->socketClient = new SocketClient($this->server_ip, $this->server_port);
        if ($this->socketClient === null) {
            $this->socketClient = new SocketClient($this->server_ip, $this->server_port);
        }

        return $this->socketClient;
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
        } catch (\Exception $e) {
            return ['status' => 'error', 'error_msg' => $e->getMessage()];
        }
        if (is_array($responseArray)) {
            return $responseArray;
        } else {
            return ['status' => 'error', 'error_msg' => 'Unknown error receiving gw response'];
        }
    }
}
