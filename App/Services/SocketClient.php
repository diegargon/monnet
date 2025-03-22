<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class SocketClient
{
    private string $host;
    private int $port;
    private \Socket $socket;

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Connect to the socket server
     *
     * @throws \Exception
     */
    public function connect(): void
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            throw new \Exception('Socket creation failed: ' . socket_strerror(socket_last_error()));
        }

        $result = socket_connect($this->socket, $this->host, $this->port);
        if ($result === false) {
            throw new \Exception('Socket connection failed: ' . socket_strerror(socket_last_error($this->socket)));
        }
    }

    /**
     * Send data and receive response
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function sendAndReceive(array $data): array
    {
        $this->connect();

        $encodedData = json_encode($data);
        if ($encodedData === false) {
            throw new \Exception('Invalid JSON encoding: ' . json_last_error_msg());
        }

        socket_write($this->socket, $encodedData, strlen($encodedData));

        $response = $this->readResponse();

        socket_close($this->socket);

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON decode error: ' . json_last_error_msg());
        }

        return $decodedResponse;
    }

    /**
     * Read response from socket
     *
     * @return string
     * @throws \Exception
     */
    private function readResponse(): string
    {
        $response = '';
        $openBraces = 0;
        $jsonComplete = false;

        while (!$jsonComplete) {
            $chunk = socket_read($this->socket, 1024);
            if ($chunk === false) {
                throw new \Exception('Error reading socket: ' . socket_strerror(socket_last_error($this->socket)));
            }
            if ($chunk === '') {
                throw new \Exception('Chunk Error reading socket: Incomplete JSON response');
            }

            $response .= $chunk;

            // Count opened and closed braces
            foreach (str_split($chunk) as $char) {
                if ($char === '{' || $char === '[') {
                    $openBraces++;
                } elseif ($char === '}' || $char === ']') {
                    $openBraces--;
                }
            }

            if ($openBraces === 0 && trim($response) !== '') {
                $jsonComplete = true;
            }
        }

        return $response;
    }
}
