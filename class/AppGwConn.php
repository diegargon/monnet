<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

class AppGwConn {
    private $socket;
    private $serverIp;
    private $serverPort;

    public function __construct(string $serverIp, int $serverPort) {
        $this->serverIp = $serverIp;
        $this->serverPort = $serverPort;
    }

    public function sendCommand(array $data): array {
        $this->connect();
        $this->sendData($data);
        $response = $this->readResponse();
        $this->close();

        return $response;
    }

    private function connect(): void {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            throw new RuntimeException('Socket creation failed: ' . socket_strerror(socket_last_error()));
        }

        if (!socket_connect($this->socket, $this->serverIp, $this->serverPort)) {
            throw new RuntimeException('Connection failed: ' . socket_strerror(socket_last_error($this->socket)));
        }
    }

    private function sendData(array $data): void {
        $jsonData = json_encode($data);
        if ($jsonData === false) {
            throw new RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }

        socket_write($this->socket, $jsonData, strlen($jsonData));
    }

    private function readResponse(): array {
        $response = '';
        $openBraces = 0;

        do {
            $chunk = socket_read($this->socket, 1024);
            if ($chunk === false || $chunk === '') {
                throw new RuntimeException('Read error: ' . socket_strerror(socket_last_error($this->socket)));
            }

            $response .= $chunk;
            $openBraces += substr_count($chunk, '{') - substr_count($chunk, '}');
        } while ($openBraces > 0);

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSON decode error: ' . json_last_error_msg());
        }

        return $decoded;
    }

    private function close(): void {
        if ($this->socket) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }
}